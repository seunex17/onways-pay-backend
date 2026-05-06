<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Climactic\Credits\Traits\HasCredits;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasCredits, HasFactory, Notifiable;

    protected $fillable = [
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
        'profile_photo',
        'gender',
        'date_of_birth',
        'address',
        'city',
        'enable_push_notification',
        'account_delete_at'
    ];

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
            'enable_push_notification' => 'boolean',
            'account_delete_at' => 'datetime',
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

    /**
     * Get the deposits for the user.
     *
     * @return HasMany<Deposit, $this>
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Get the exchanges for the user.
     *
     * @return HasMany<Exchange, $this>
     */
    public function exchanges(): HasMany
    {
        return $this->hasMany(Exchange::class);
    }

    /**
     * Get the payment requests for the user.
     *
     * @return HasMany<PaymentRequest, $this>
     */
    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    /**
     * Get the withdrawals for the user.
     *
     * @return HasMany<Withdrawal, $this>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
