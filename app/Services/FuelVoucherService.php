<?php

namespace App\Services;

use App\Events\FuelVoucherIssued;
use App\Events\TransactionStatusEvent;
use App\Models\FuelVoucher;
use App\Models\FuelVoucherPurchase;
use App\Models\MomoPayment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FuelVoucherService
{
    /** @param array<string, mixed> $data */
    public function purchase(User $user, array $data, string $idempotencyKey): FuelVoucherPurchase
    {
        if (FuelVoucherPurchase::query()->where('user_id', $user->id)->where('idempotency_key', $idempotencyKey)->exists()) {
            throw new ConflictHttpException(__('fuel_voucher_idempotency_key_used'));
        }

        $pricePerLitre = (float) config('fuel-vouchers.price_per_litre');

        if ($pricePerLitre <= 0) {
            throw new UnprocessableEntityHttpException(__('fuel_voucher_price_not_configured'));
        }

        $recipientName = $data['recipient_type'] === 'self'
            ? trim((string) ($user->first_name.' '.$user->last_name)) ?: $user->name
            : $data['recipient_name'];
        $recipientPhone = $data['recipient_type'] === 'self' ? $user->phone : $data['recipient_phone'];

        if (! is_string($recipientPhone) || ! preg_match('/^[0-9]{6,15}$/', $recipientPhone)) {
            throw new UnprocessableEntityHttpException(__('fuel_voucher_recipient_phone_required'));
        }

        $amount = round((float) $data['litres'] * $pricePerLitre, 2);
        $purchase = DB::transaction(function () use ($user, $data, $recipientName, $recipientPhone, $idempotencyKey, $amount): FuelVoucherPurchase {
            $transaction = Transaction::query()->create([
                'user_id' => $user->id,
                'type' => 'debit',
                'purpose' => 'fuel_voucher',
                'amount' => $amount,
                'description' => __('fuel_voucher_transaction_description', ['litres' => $this->litres($data['litres'])]),
                'status' => 'pending',
                'reference' => $this->reference('FUEL-TXN'),
                'payment_method' => 'Mobile Money',
            ]);

            return FuelVoucherPurchase::query()->create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'provider' => $data['payment_provider'],
                'litres' => $data['litres'],
                'amount' => $amount,
                'currency' => mb_strtoupper((string) config('fuel-vouchers.currency', 'XOF')),
                'recipient_type' => $data['recipient_type'],
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'recipient_phone_code' => $data['recipient_phone_code'],
                'payment_phone' => $data['payment_phone'],
                'payment_phone_code' => $data['payment_phone_code'],
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
            ]);
        });

        $payment = TouchPayService::collectPayment([
            'email' => $user->email,
            'firstname' => $user->first_name,
            'lastname' => $user->last_name,
            'mobile_number' => $purchase->payment_phone,
            'amount' => $purchase->amount,
            'provider' => $purchase->provider,
            'transaction_ref' => $purchase->transaction->reference,
        ]);

        if (! $payment['status']) {
            $purchase->update(['status' => 'failed']);
            $purchase->transaction()->update(['status' => 'failed']);

            throw new HttpException(502, __('service_not_available'));
        }

        $touchPayData = $payment['data'];
        $purchase->update([
            'status' => 'awaiting_authorization',
            'touchpay_reference' => $touchPayData['idFromClient'] ?? $purchase->transaction->reference,
        ]);

        MomoPayment::query()->create([
            'transaction_id' => $purchase->transaction_id,
            'reference' => $touchPayData['idFromClient'] ?? $purchase->transaction->reference,
            'amount' => $touchPayData['amount'] ?? $purchase->amount,
            'fee' => $touchPayData['fees'] ?? 0,
            'service' => ucwords($purchase->provider),
            'service_code' => $touchPayData['serviceCode'] ?? TouchPayService::serviceCode[$purchase->provider],
            'recipient_number' => $touchPayData['recipientNumber'] ?? $purchase->payment_phone,
            'status' => $touchPayData['status'] ?? 'PENDING',
        ]);

        return $purchase->fresh(['transaction', 'voucher']);
    }

    public function complete(FuelVoucherPurchase $purchase): FuelVoucherPurchase
    {
        $purchase = DB::transaction(function () use ($purchase): FuelVoucherPurchase {
            $locked = FuelVoucherPurchase::query()->lockForUpdate()->findOrFail($purchase->id);

            if (! $locked->voucher()->exists()) {
                $code = $this->voucherCode();
                $reference = $this->reference('FUEL-VCH');

                FuelVoucher::query()->create([
                    'fuel_voucher_purchase_id' => $locked->id,
                    'user_id' => $locked->user_id,
                    'reference' => $reference,
                    'code' => $code,
                    'code_hash' => hash('sha256', $code),
                    'qr_data' => Crypt::encryptString(json_encode([
                        'reference' => $reference,
                        'code' => $code,
                    ], JSON_THROW_ON_ERROR)),
                    'status' => 'active',
                    'litres' => $locked->litres,
                    'amount' => $locked->amount,
                    'currency' => $locked->currency,
                    'recipient_name' => $locked->recipient_name,
                    'recipient_phone' => $locked->recipient_phone,
                    'expires_at' => now()->addDays((int) config('fuel-vouchers.validity_days', 30))->endOfDay(),
                ]);
            }

            $locked->update(['status' => 'completed']);
            $locked->transaction()->update(['status' => 'completed']);

            return $locked->fresh(['transaction', 'voucher']);
        });

        TransactionStatusEvent::dispatch($purchase->transaction);
        FuelVoucherIssued::dispatch($purchase);

        return $purchase;
    }

    private function reference(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.mb_strtoupper(Str::random(8));
    }

    private function voucherCode(): string
    {
        return implode('-', [random_int(1000, 9999), random_int(1000, 9999), random_int(1000, 9999)]);
    }

    private function litres(mixed $litres): string
    {
        return rtrim(rtrim(number_format((float) $litres, 2, '.', ''), '0'), '.');
    }
}
