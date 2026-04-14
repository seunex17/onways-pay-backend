<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
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
            'type' => $this->faker->randomElement(['debit', 'credit']),
            'purpose' => $this->faker->randomElement(['deposit', 'withdraw', 'transfer', 'exchange']),
            'status' => $this->faker->randomElement(['pending', 'confirming', 'processing', 'processed', 'canceled', 'expired']),
            'reference' => $this->faker->unique()->bothify('TRX-########'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'payment_method' => $this->faker->randomElement(['momo', 'crypto', 'card']),
            'description' => $this->faker->sentence(),
        ];
    }
}
