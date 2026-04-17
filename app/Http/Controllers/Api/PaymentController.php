<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: PaymentController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/16/26
 * Time: 1:41 PM
 */

namespace App\Http\Controllers\Api;

use App\Events\PaymentRequestEvent;
use App\Events\TransactionStatusEvent;
use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use App\Models\TransactionPin;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\AccountService;
use App\Services\CryptoPaymentService;
use App\Services\ExchangeService;
use App\Services\TouchPayService;
use Climactic\Credits\Exceptions\InsufficientCreditsException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Sqids\Sqids;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PaymentController extends Controller
{
    public function request(Request $request)
    {
        if ($request->amount <= 0) {
            return response()->json([
                'message' => __('invalid_amount'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $requestFee = config('fees.request');
        $fee = ($requestFee / 100) * $request->amount;

        $paymentRequest = PaymentRequest::create([
            'user_id' => $request->user()->id,
            'reference' => 'OWP-'.time(),
            'amount' => $request->amount,
            'fee' => $fee,
            'expired_at' => now()->addMinutes(30),
            'description' => $request->description,
        ]);

        $sqids = new Sqids(minLength: 10);
        $qrData = $sqids->encode([$paymentRequest->id]);

        return response()->json([
            'payment_request' => PaymentRequest::find($paymentRequest->id),
            'qr_data' => $qrData,
        ]);
    }

    /**
     * @throws ConnectionException
     * @throws InsufficientCreditsException
     */
    public function withdraw(Request $request)
    {
        $amount = $request->amount;
        $exchangeAmount = $amount;
        $transactionFee = 0;

        $transactionPin = TransactionPin::where('user_id', $request->user()->id)->first();

        if (! Hash::check($request->pin, $transactionPin->pin)) {
            return response()->json([
                'message' => __('invalid_transaction_pin'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! $request->user()->hasCredits($amount)) {
            return response()->json([
                'message' => __('insufficient_balance'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($request->input('method') === 'crypto') {
            $rate = ExchangeService::rate(config('exchange.currency'), 'USD');
            $exchangeAmount = $rate * $amount;

            if ($exchangeAmount < 10) {
                return response()->json([
                    'message' => __('invalid_amount'),
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $transactionFee = (config('fees.withdrawal') / 100) * $exchangeAmount;
        }

        if ($request->amoun < 10) {
            return response()->json([
                'message' => __('invalid_amount'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $transactionFee = (config('fees.withdrawal') / 100) * $exchangeAmount;

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'type' => 'debit',
            'purpose' => 'withdraw',
            'reference' => 'OWP-WTH-'.time(),
            'amount' => $amount,
            'payment_method' => 'Wallet',
        ]);

        $withdrawal = Withdrawal::create([
            'user_id' => $request->user()->id,
            'transaction_id' => $transaction->id,
            'method' => $request->input('method'),
            'currency' => $request->input('currency'),
            'destination' => $request->destination,
            'amount' => $exchangeAmount,
            'fee' => $transactionFee,
        ]);

        if ($request->input('method') === 'momo') {
            $touchPay = TouchPayService::sendMoney([
                'transaction_ref' => $transaction->reference,
                'email' => $request->user()->email,
                'firstname' => $request->user()->first_name,
                'lastname' => $request->user()->last_name,
                'amount' => $withdrawal->amount - $withdrawal->fee,
                'mobile_number' => $withdrawal->destination,
                'provider' => $withdrawal->currency,
            ]);

            if (! $touchPay['status']) {
                $transaction->status = 'processing';
                $transaction->save();

                $withdrawal->status = 'hold';
            } else {
                $transaction->status = 'processed';
                $transaction->save();

                $withdrawal->status = 'success';
            }
            $withdrawal->save();
            $request->user()->creditDeduct($withdrawal->amount, 'Withdrawal to '.$withdrawal->destination);
        } else {
            $amount = $withdrawal->amount - $withdrawal->fee;

            $cryptoPayment = CryptoPaymentService::payout(
                $amount,
                $transaction->reference,
                $withdrawal->destination,
                $withdrawal->currency,
            );

            if (! $cryptoPayment['status'] && $cryptoPayment['data'] === null) {
                $transaction->status = 'processing';
                $transaction->save();

                $withdrawal->status = 'hold';
                $withdrawal->save();
            }

            $request->user()->creditDeduct($withdrawal->amount, 'Withdrawal to '.$withdrawal->destination." ($withdrawal->currency)");
        }

        return response()->json([
            'transaction' => Transaction::find($transaction->id),
        ], ResponseAlias::HTTP_OK);
    }

    public function scanCode(Request $request)
    {
        $code = $request->code;
        $sqids = new Sqids(minLength: 10);

        $qrData = $sqids->decode($code);
        if (empty($qrData) || count($qrData) !== 1) {
            return response()->json([
                'message' => __('invalid_payment_code'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $paymentRequestId = $qrData[0];

        $paymentRequest = PaymentRequest::with('user')
            ->find($paymentRequestId);

        if (! $paymentRequest) {
            return response()->json([
                'message' => __('invalid_payment_code'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($paymentRequest->user_id === $request->user()->id) {
            return response()->json([
                'message' => __('you_attempt_to_make_invalid_payment'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($paymentRequest->expired_at->isPast()) {
            $paymentRequest->status = 'expired';
            $paymentRequest->save();

            return response()->json([
                'message' => __('payment_expired'),
            ]);
        }

        if (! $request->user()->hasCredits($paymentRequest->amount)) {
            return response()->json([
                'message' => __('insufficient_balance'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $paymentRequest->status = 'initiate';
        $paymentRequest->save();
        broadcast(new PaymentRequestEvent($paymentRequest))->toOthers();

        Transaction::create([
            'user_id' => $request->user()->id,
            'type' => 'debit',
            'purpose' => 'transfer',
            'reference' => $paymentRequest->reference,
            'amount' => $paymentRequest->amount,
        ]);

        Transaction::create([
            'user_id' => $paymentRequest->user_id,
            'type' => 'credit',
            'purpose' => 'transfer',
            'reference' => $paymentRequest->reference,
            'amount' => $paymentRequest->amount,
        ]);

        return response()->json($paymentRequest, ResponseAlias::HTTP_OK);
    }


    /**
     * @throws \Climactic\Credits\Exceptions\InsufficientCreditsException
     */
    public function makePayment(Request $request)
    {
        $paymentRequest = PaymentRequest::with('user')
            ->find($request->id);

        if (! $paymentRequest) {
            return response()->json([
                'message' => __('invalid_payment_code'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if (! AccountService::verifyPin($request->user(), $request->pin)) {
            return response()->json([
                'message' => __('invalid_transaction_pin'),
            ]);
        }

        if (! $request->user()->hasCredits($paymentRequest->amount)) {
            return response()->json([
                'message' => __('insufficient_balance'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $amount = $paymentRequest->amount - $paymentRequest->fee;

        /** @var User $recipient */
        $recipient = $paymentRequest->user;

        $request->user()->creditDeduct($paymentRequest->amount, $paymentRequest->description);
        $recipient->creditAdd($amount, $paymentRequest->description);

        $paymentRequest->status = 'complete';
        $paymentRequest->save();

        Transaction::where('reference', $paymentRequest->reference)->update([
            'status' => 'processed',
        ]);

        $receiverTransaction = Transaction::where([
            'user_id' => $paymentRequest->user_id,
            'reference' => $paymentRequest->reference,
        ])->first();

        broadcast(new TransactionStatusEvent($receiverTransaction))->toOthers();

        return response()->json([
            'transaction' => Transaction::where([
                'user_id' => $request->user()->id,
                'reference' => $paymentRequest->reference,
            ])->first(),
        ], ResponseAlias::HTTP_OK);
    }
}
