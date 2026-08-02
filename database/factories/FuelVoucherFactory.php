<?php

namespace Database\Factories;

use App\Models\FuelVoucher;
use App\Models\FuelVoucherPurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelVoucher>
 */
class FuelVoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fuel_voucher_purchase_id' => FuelVoucherPurchase::factory(),
            'user_id' => User::factory(),
            'reference' => 'FUEL-VCH-'.fake()->unique()->numerify('########'),
            'code' => fake()->numerify('####-####-####'),
            'code_hash' => hash('sha256', fake()->unique()->uuid()),
            'qr_data' => fake()->uuid(),
            'status' => 'active',
            'litres' => 20,
            'amount' => 25000,
            'currency' => 'XOF',
            'recipient_name' => fake()->name(),
            'recipient_phone' => '0701020304',
            'expires_at' => now()->addMonth(),
        ];
    }
}
