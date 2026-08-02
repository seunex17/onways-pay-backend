<?php

namespace Database\Factories;

use App\Models\FuelStation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelStation>
 */
class FuelStationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'station_id' => 'STATION-'.fake()->unique()->numerify('###'),
            'name' => fake()->company(),
            'mobile_money_phone' => '0506070809',
            'mobile_money_provider' => 'orange-money',
            'is_active' => true,
        ];
    }
}
