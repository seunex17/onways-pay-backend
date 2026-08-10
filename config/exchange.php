<?php

return [
    'api_key' => env('EXCHANGE_API_KEY'),
    'currency' => env('EXCHANGE_CURRENCY', 'XOF'),
    'min_amount' => env('EXCHANGE_MIN_AMOUNT', 0),
];
