<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: CryptoPaymentService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/3/26
 * Time: 11:26 AM
 */

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CryptoPaymentService
{
    const string BASE_URL = 'https://api.oxapay.com/v1/';

    const array TRC20_NETOWRK = [
        'USDT',
    ];

    /**
     * @throws ConnectionException
     */
    public static function whiteLabel(float $amount, string $orderID, string $currency = 'USDT'): array
    {
        $network = match ($currency) {
            'USDT' => 'TRC20',
            'BTC' => 'Bitcoin',
            'ETH', 'USDC' => 'Ethereum',
            'BNB' => 'BSC',
            'DOGE' => 'Dogecoin',
            'LTC' => 'Litecoin',
            'SOL' => 'Solana',
            'TRX' => 'Tron',
            'SHIB' => 'BSC',
            'TON' => 'The Open Network',
            'XMR' => 'Monero',
            default => '',
        };

        $response = Http::withHeaders([
            'merchant_api_key' => config('oxapay.api_key'),
        ])->post(self::BASE_URL.'payment/white-label', [
            'amount' => $amount,
            'currency' => 'USD',
            'pay_currency' => $currency,
            'network' => $network,
            'order_id' => $orderID,
            'callback_url' => 'https://onwayspay.zubdev.net/webhook/oxapay',
            // 'callback_url' => route('webhook.oxapay'),
        ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'data' => $response->json()['data'],
            ];
        }

        return [
            'status' => false,
            'message' => $response->json(),
        ];
    }
}
