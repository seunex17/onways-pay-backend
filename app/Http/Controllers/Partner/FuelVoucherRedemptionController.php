<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedeemFuelVoucherRequest;
use App\Models\FuelStation;
use App\Models\FuelVoucher;
use App\Services\TouchPayService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FuelVoucherRedemptionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RedeemFuelVoucherRequest $request): JsonResponse
    {
        $configuredSecret = (string) config('fuel-vouchers.redemption_secret');
        $providedSecret = (string) $request->bearerToken();
        abort_unless($configuredSecret !== '' && hash_equals($configuredSecret, $providedSecret), Response::HTTP_UNAUTHORIZED);

        $data = $request->validated();
        $code = $this->redemptionCode($data);
        $station = FuelStation::query()
            ->where('station_id', $data['station_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $voucher = DB::transaction(function () use ($data, $code): FuelVoucher {
                $voucher = FuelVoucher::query()->where('code_hash', hash('sha256', $code))
                    ->lockForUpdate()->firstOrFail();

                if ($voucher->status !== 'active' || $voucher->expires_at->isPast()) {
                    abort(Response::HTTP_CONFLICT, __('fuel_voucher_cannot_be_redeemed'));
                }

                $voucher->update([
                    'status' => 'processing',
                    'station_id' => $data['station_id'],
                    'partner_redemption_id' => $data['partner_redemption_id'],
                    'redemption_actor' => $data['terminal_id'] ?? null,
                    'redeemed_at' => now(),
                ]);

                return $voucher;
            });
        } catch (UniqueConstraintViolationException) {
            abort(Response::HTTP_CONFLICT, __('fuel_voucher_redemption_already_processed'));
        }

        $payoutReference = 'FUEL-RDM-'.$voucher->id.'-'.$data['partner_redemption_id'];
        $payout = TouchPayService::sendMoney([
            'transaction_ref' => $payoutReference,
            'amount' => $voucher->amount,
            'mobile_number' => $station->mobile_money_phone,
            'provider' => $station->mobile_money_provider,
        ]);

        if (! $payout['status']) {
            $voucher->update([
                'status' => 'active',
                'station_id' => null,
                'partner_redemption_id' => null,
                'redemption_actor' => null,
                'redeemed_at' => null,
            ]);

            return response()->json(['message' => __('fuel_voucher_station_payment_failed')], Response::HTTP_BAD_GATEWAY);
        }

        $voucher->update([
            'status' => 'redeemed',
            'payout_reference' => $payoutReference,
        ]);

        return response()->json([
            'status' => 'redeemed',
            'reference' => $voucher->reference,
            'redeemed_at' => $voucher->redeemed_at->toISOString(),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function redemptionCode(array $data): string
    {
        if (filled($data['code'] ?? null)) {
            return $data['code'];
        }

        try {
            $payload = json_decode(Crypt::decryptString($data['qr_data']), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('invalid_payment_code'));
        }

        if (! is_array($payload) || ! is_string($payload['code'] ?? null)) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('invalid_payment_code'));
        }

        return $payload['code'];
    }
}
