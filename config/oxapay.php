<?php

return [
    'api_key' => env('OXAPAY_APIKEY', ''),
    'callback_url' => env('OXAPAY_CALLBACK_URL', ''),
    'exchange_callback_url' => env('OXAPAY_EXCHANGE_CALLBACK_URL', ''),
    'general_api_key' => env('OXAPAY_GENERAL_APIKEY', ''),
    'payout_api_key' => env('OXAPAY_PAYOUT_APIKEY', ''),
    'withdrawal_callback_url' => env('OXAPAY_WITHDRAWAL_CALLBACK_URL', ''),
];
