<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: TouchPayService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/14/26
 * Time: 8:54 AM
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TouchPayService
{
    const array serviceCode = [
        'mtn' => 'PAIEMENTMARCHAND_MTN_CI',
        'moov' => 'PAIEMENTMARCHAND_MOOV_CI',
        'orange-money' => 'PAIEMENTMARCHANDOMPAYCIDIRECT',
        'wave' => 'CI_PAIEMENTWAVE_TP',
    ];

    const array cashInServiceCode = [
        'mtn' => 'CASHINMTNPART',
        'orange-money' => 'CASHINOMCIPART',
        'moov' => 'CASHINMOOVPART',
        'wave' => 'CI_CASHIN_WAVE_PART',
    ];

    public static function collectPayment(array $data)
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode(config('touchpay.username').':'.config('touchpay.password')),
            ])
            ->put('https://apidist.gutouch.net/apidist/sec/touchpayapi/WINTA9061/transaction?loginAgent='.config('touchpay.login_agent').'&passwordAgent='.config('touchpay.password_agent'), [
                'idFromClient' => $data['transaction_ref'],
                'additionnalInfos' => [
                    'recipientEmail' => $data['email'],
                    'recipientFirstName' => $data['firstname'],
                    'recipientLastName' => $data['lastname'],
                    'destinataire' => $data['mobile_number'],
                    'otp' => $data['otp'] ?? '',
                    'partner_name' => 'OnwaysPay Solution',
                    'return_url' => config('app.url'),
                    'cancel_url' => config('app.url'),
                ],
                'amount' => $data['amount'],
                'callback' => config('touchpay.callback_url'),
                'recipientNumber' => $data['mobile_number'],
                'serviceCode' => self::serviceCode[$data['provider']],
            ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'status' => false,
            'message' => $response->json()['detailMessage'],
        ];
    }

    public static function checkBalance(): array
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode(config('touchpay.username').':'.config('touchpay.password')),
            ])
            ->post('https://apidist.gutouch.net/apidist/sec/WINTA9061/get_balance', [
                'partner_id' => config('touchpay.partner_id'),
                'login_api' => config('touchpay.login_agent'),
                'password_api' => config('touchpay.password_agent'),
            ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'status' => false,
            'message' => $response->json()['errorMessage'],
        ];
    }

    public static function sendMoney(array $data)
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode(config('touchpay.username').':'.config('touchpay.password')),
            ])
            ->post('https://apidist.gutouch.net/apidist/sec/WINTA9061/cashin', [
                'partner_transaction_id' => $data['transaction_ref'],
                'partner_id' => config('touchpay.partner_id'),
                'amount' => $data['amount'],
                'call_back_url' => config('touchpay.callback_url'),
                'recipient_phone_number' => $data['mobile_number'],
                'service_id' => self::cashInServiceCode[$data['provider']],
                'login_api' => config('touchpay.login_agent'),
                'password_api' => config('touchpay.password_agent'),
            ]);

        return $response->json();

        if ($response->successful()) {
            return [
                'status' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'status' => false,
            'message' => $response->json()['detailMessage'],
        ];
    }

    protected static function baseUrl(string $param): string
    {
        $loginAgent = config('touchpay.login_agent');
        $passwordAgent = config('touchpay.password_agent');

        return "https://api.gutouch.com/dist/api/touchpayapi/v1/WINTA9061/$param?loginAgent=$loginAgent&passwordAgent=$passwordAgent";
    }
}
