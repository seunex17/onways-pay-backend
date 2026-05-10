<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangePayout extends Model
{
    protected $fillable = [
        'exchange_id',
        'uuid',
        'tries',
        'next_retry',
        'reason',
        'status',
        'payment_reference',
    ];

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }
}
