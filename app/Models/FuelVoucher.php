<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\FuelVoucherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon $expires_at
 * @property Carbon|null $redeemed_at
 */
class FuelVoucher extends Model
{
    /** @use HasFactory<FuelVoucherFactory> */
    use HasFactory;

    protected $fillable = [
        'fuel_voucher_purchase_id', 'user_id', 'reference', 'code', 'code_hash', 'qr_data',
        'status', 'litres', 'amount', 'currency', 'recipient_name', 'recipient_phone',
        'expires_at', 'payout_reference', 'station_id', 'partner_redemption_id',
        'redemption_actor', 'redeemed_at',
    ];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'code' => 'encrypted', 'qr_data' => 'encrypted', 'recipient_phone' => 'encrypted',
            'litres' => 'decimal:2', 'amount' => 'decimal:2', 'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<FuelVoucherPurchase, $this> */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(FuelVoucherPurchase::class, 'fuel_voucher_purchase_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
