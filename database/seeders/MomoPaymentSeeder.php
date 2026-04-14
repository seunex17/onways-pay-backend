<?php

namespace Database\Seeders;

use App\Models\MomoPayment;
use Illuminate\Database\Seeder;

class MomoPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MomoPayment::factory()->count(10)->create();
    }
}
