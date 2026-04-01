<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'pin'])]
class TransactionPin extends Model
{
    protected function casts(): array
    {
        return [
            'pin' => 'hashed',
        ];
    }

    /**
     * Get the user that owns the transaction pin.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
