<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPin extends Model
{
    protected $fillable = ['user_id', 'pin'];

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
