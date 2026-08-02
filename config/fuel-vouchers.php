<?php

return [
    'price_per_litre' => (float) env('FUEL_PRICE_PER_LITRE', 0),
    'currency' => env('FUEL_VOUCHER_CURRENCY', 'XOF'),
    'validity_days' => (int) env('FUEL_VOUCHER_VALIDITY_DAYS', 30),
    'redemption_secret' => env('FUEL_STATION_REDEMPTION_SECRET'),
];
