<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Climactic\Credits\Traits\HasCredits;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'uuid',
    'customer_id',
    'first_name',
    'last_name',
    'country',
    'country_code',
    'phone',
    'status',
    'phone_verified_at',
    'name',
    'email',
    'password',
    'middle_name',
    'phone_code',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasCredits, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the transaction pin associated with the user.
     *
     * @return HasOne<TransactionPin, $this>
     */
    public function transactionPin(): HasOne
    {
        return $this->hasOne(TransactionPin::class);
    }
}
