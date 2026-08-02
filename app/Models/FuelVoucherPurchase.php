<?php

namespace App\Models;

use Database\Factories\FuelVoucherPurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FuelVoucherPurchase extends Model
{
    /** @use HasFactory<FuelVoucherPurchaseFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'transaction_id', 'provider', 'litres', 'amount', 'currency',
        'recipient_type', 'recipient_name', 'recipient_phone', 'recipient_phone_code',
        'payment_phone', 'payment_phone_code', 'status', 'idempotency_key',
        'touchpay_reference',
    ];

    protected function casts(): array
    {
        return [
            'litres' => 'decimal:2', 'amount' => 'decimal:2',
            'recipient_phone' => 'encrypted', 'payment_phone' => 'encrypted',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Transaction, $this> */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /** @return HasOne<FuelVoucher, $this> */
    public function voucher(): HasOne
    {
        return $this->hasOne(FuelVoucher::class);
    }
}
