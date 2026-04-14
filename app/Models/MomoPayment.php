<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MomoPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'reference',
        'amount',
        'fee',
        'service',
        'service_code',
        'recipient_number',
        'status',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
