<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: PaymentController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/16/26
 * Time: 1:41 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Sqids\Sqids;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PaymentController extends Controller
{
    public function request(Request $request)
    {
        if ($request->amount <= 0) {
            return response()->json([
                'message' => __('invalid_amount'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $requestFee = config('fees.request');
        $fee = ($requestFee / 100) * $request->amount;

        $paymentRequest = PaymentRequest::create([
            'user_id' => $request->user()->id,
            'reference' => 'OWP-'.time(),
            'amount' => $request->amount,
            'fee' => $fee,
            'expired_at' => now()->addMinutes(30),
            'description' => $request->description,
        ]);

        $sqids = new Sqids(minLength: 10);
        $qrData = $sqids->encode([$paymentRequest->id]);

        return response()->json([
            'payment_request' => PaymentRequest::find($paymentRequest->id),
            'qr_data' => $qrData,
        ]);
    }
}
