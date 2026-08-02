<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'purpose',
        'amount',
        'description',
        'status',
        'reference',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    /**
     * Get the user that owns the transaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the deposit associated with the transaction.
     *
     * @return HasOne<Deposit, $this>
     */
    public function deposit(): HasOne
    {
        return $this->hasOne(Deposit::class);
    }

    /**
     * Get the momo payment associated with the transaction.
     *
     * @return HasOne<MomoPayment, $this>
     */
    public function momoPayment(): HasOne
    {
        return $this->hasOne(MomoPayment::class);
    }

    /**
     * Get the exchange associated with the transaction.
     *
     * @return HasOne<Exchange, $this>
     */
    public function exchange(): HasOne
    {
        return $this->hasOne(Exchange::class);
    }

    /**
     * Get the gift card order associated with the transaction.
     *
     * @return HasOne<GiftCardOrder, $this>
     */
    public function giftCardOrder(): HasOne
    {
        return $this->hasOne(GiftCardOrder::class);
    }

    /** @return HasOne<FuelVoucherPurchase, $this> */
    public function fuelVoucherPurchase(): HasOne
    {
        return $this->hasOne(FuelVoucherPurchase::class);
    }

    /**
     * Get the withdrawal associated with the transaction.
     *
     * @return HasOne<Withdrawal, $this>
     */
    public function withdrawal(): HasOne
    {
        return $this->hasOne(Withdrawal::class);
    }
}
