<?php

namespace Database\Factories;

use App\Models\MomoPayment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MomoPayment>
 */
class MomoPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'reference' => $this->faker->unique()->bothify('MOMO-########'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'fee' => $this->faker->randomFloat(2, 0, 10),
            'service' => $this->faker->randomElement(['Orange Money', 'MTN Momo']),
            'service_code' => $this->faker->randomElement(['OM', 'MTN']),
            'recipient_number' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'message' => $this->faker->sentence(),
        ];
    }
}
