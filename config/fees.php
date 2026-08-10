<?php

return [
    'exchange' => env('FEES_EXCHANGE', 0),
    'request' => env('FEE_REQUEST', 0),
    'withdrawal' => env('FEE_WITHDRAWAL', 0),
    'gift_card' => env('FEE_GIFT_CARD', 0),
    'maintenance_fee' => env('FEE_MAINTENANCE_FEE', 100),
];
