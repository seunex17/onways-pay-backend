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

    public static function collectPayment(array $data)
    {
        $response = Http::withoutVerifying()
            ->withDigestAuth(config('touchpay.username'), config('touchpay.password'))
            ->put(self::baseUrl('transaction'), [
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

    protected static function baseUrl(string $param): string
    {
        $loginAgent = config('touchpay.login_agent');
        $passwordAgent = config('touchpay.password_agent');

        return "https://api.gutouch.com/dist/api/touchpayapi/v1/WINTA9061/$param?loginAgent=$loginAgent&passwordAgent=$passwordAgent";
    }
}
