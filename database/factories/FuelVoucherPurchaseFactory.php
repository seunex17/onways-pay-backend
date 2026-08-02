<?php

namespace Database\Factories;

use App\Models\FuelVoucherPurchase;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelVoucherPurchase>
 */
class FuelVoucherPurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'transaction_id' => Transaction::factory(),
            'provider' => 'mtn',
            'litres' => 20,
            'amount' => 25000,
            'currency' => 'XOF',
            'recipient_type' => 'self',
            'recipient_name' => fake()->name(),
            'recipient_phone' => '0701020304',
            'recipient_phone_code' => '225',
            'payment_phone' => '0506070809',
            'payment_phone_code' => '225',
            'status' => 'awaiting_authorization',
            'idempotency_key' => fake()->uuid(),
        ];
    }
}
