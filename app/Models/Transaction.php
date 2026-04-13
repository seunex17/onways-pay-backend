<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
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
}
