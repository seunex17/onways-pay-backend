<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCardOrder;
use App\Models\Transaction;
use App\Models\TransactionPin;
use App\Models\User;
use App\Services\GiftCardService;
use App\Services\TouchPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class GiftCardController extends Controller
{
    public function countries()
    {
        $response = GiftCardService::countries();

        return $this->providerResponse($response);
    }

    public function products(Request $request)
    {
        $response = GiftCardService::products([
            'countryCode' => $request->country,
            'productName' => $request->product_name,
            'page' => $request->page,
            'size' => $request->size,
        ]);

        return $this->providerResponse($response);
    }

    public function product(string $product)
    {
        $response = GiftCardService::product($product);

        return $this->providerResponse($response);
    }

    public function redeemInstructions(string $product)
    {
        $response = GiftCardService::redeemInstructions($product);

        return $this->providerResponse($response);
    }

    public function orders(Request $request)
    {
        $orders = GiftCardOrder::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json($orders, ResponseAlias::HTTP_OK);
    }

    public function order(Request $request, GiftCardOrder $giftCardOrder)
    {
        if ($giftCardOrder->user_id !== $request->user()->id) {
            return response()->json([
                'message' => __('service_not_available'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->json($giftCardOrder->load('transaction'), ResponseAlias::HTTP_OK);
    }

    public function purchase(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'product_id' => ['required', 'integer'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'quantity' => ['required', 'integer', 'min:1'],
            'recipient_email' => ['nullable', 'email'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'transaction_pin' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        // Calculate amount
        $fxRes = GiftCardService::fxRates('XOF', 1000);

        $senderAmount = $fxRes['senderAmount'];
        $recipientAmount = $fxRes['recipientAmount'];
        $xofPerDollar = $recipientAmount / $senderAmount;
        $giftCardUsdPrice = $request->unit_price * $request->quantity;
        $baseXofCost = $giftCardUsdPrice * $xofPerDollar;
        $myMarkup = config('fees.gift_card');
        $totalXofToDebit = $baseXofCost * (1 + $myMarkup);
        $finalUserDebit = ceil($totalXofToDebit);

        /** @var User $user */
        $user = $request->user();
        $totalAmount = (float) $request->unit_price * (int) $request->quantity;
        $transactionPin = TransactionPin::where('user_id', $user->id)->first();

        $reference = 'OWP-GC-'.now()->timestamp.'-'.$user->id;

        if ($request->payment_method === 'wallet') {
            if (! $transactionPin || ! Hash::check($request->transaction_pin, $transactionPin->pin)) {
                return response()->json([
                    'message' => __('invalid_transaction_pin'),
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            if (! $user->hasCredits($finalUserDebit)) {
                return response()->json([
                    'message' => __('insufficient_balance'),
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $user->creditDeduct($finalUserDebit, $request->product_name);
        } else {
            $momoPayment = TouchPayService::collectPayment([
                'email' => $request->user()->email,
                'firstname' => $request->user()->first_name,
                'lastname' => $request->user()->last_name,
                'mobile_number' => $request->payment_phone,
                'otp' => $request->otp ?? '',
                'amount' => $finalUserDebit,
                'provider' => $request->provider,
                'transaction_ref' => $reference,
            ]);

            if (! $momoPayment['status']) {
                return response()->json([
                    'message' => $momoPayment['message'],
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }
        }

        $providerPayload = [
            'productId' => (int) $request->product_id,
            'quantity' => (int) $request->quantity,
            'unitPrice' => (float) $request->unit_price,
            'customIdentifier' => $reference,
            'senderName' => $request->sender_name ?? config('app.name'),
        ];

        if ($request->recipient_email) {
            $providerPayload['recipientEmail'] = $request->recipient_email;
        }

        $transaction = DB::transaction(function () use ($request, $user, $finalUserDebit, $totalAmount, $reference, $providerPayload): Transaction {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'purpose' => 'gift_card',
                'reference' => $reference,
                'amount' => $finalUserDebit,
                'payment_method' => ucfirst($request->payment_method),
                'description' => 'Gift card purchase',
                'status' => 'pending',
            ]);

            GiftCardOrder::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'reference' => $reference,
                'product_id' => (int) $request->product_id,
                'product_name' => $request->product_name,
                'country_code' => $request->country_code,
                'currency_code' => $request->currency_code,
                'unit_price' => (float) $request->unit_price,
                'quantity' => (int) $request->quantity,
                'total_amount' => $totalAmount,
                'recipient_email' => $request->recipient_email,
                'recipient_phone' => $request->recipient_phone,
                'status' => 'pending',
                'request_payload' => $providerPayload,
            ]);

            return $transaction;
        });

        $giftCardOrder = GiftCardOrder::where('transaction_id', $transaction->id)->firstOrFail();
        $providerResponse = GiftCardService::order($providerPayload);

        if (isset($providerResponse['error'])) {
            $user->creditAdd($totalAmount, 'Gift card refund');

            $transaction->update(['status' => 'canceled']);
            $giftCardOrder->update([
                'status' => 'failed',
                'response_payload' => $providerResponse,
            ]);

            return response()->json([
                'message' => __('service_not_available'),
                'gift_card_order' => $giftCardOrder->fresh(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $transaction->update(['status' => 'processed']);
        $giftCardOrder->update([
            'status' => 'processed',
            'reloadly_transaction_id' => $providerResponse['transactionId'] ?? $providerResponse['id'] ?? null,
            'response_payload' => $providerResponse,
        ]);

        return response()->json([
            'transaction' => $transaction->fresh(),
            'gift_card_order' => $giftCardOrder->fresh(),
        ], ResponseAlias::HTTP_OK);
    }

    protected function providerResponse(array $response)
    {
        if (isset($response['error'])) {
            return response()->json([
                'message' => __('service_not_available'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($response, ResponseAlias::HTTP_OK);
    }
}
