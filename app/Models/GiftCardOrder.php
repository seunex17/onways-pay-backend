<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCardOrder extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'reference',
        'reloadly_transaction_id',
        'product_id',
        'product_name',
        'country_code',
        'currency_code',
        'unit_price',
        'quantity',
        'total_amount',
        'recipient_email',
        'recipient_phone',
        'status',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'total_amount' => 'decimal:2',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
