<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawPayout extends Model
{
    protected $fillable = [
        'withdrawal_id',
        'uuid',
        'tries',
        'next_retry',
        'reason',
        'status',
        'payment_reference',
    ];

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }
}
