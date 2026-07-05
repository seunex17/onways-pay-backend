<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: TopupController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 7/3/26
 * Time: 9:27 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionPin;
use App\Services\TopUpService;
use App\Services\TouchPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TopupController extends Controller
{
    public function operators(Request $request)
    {
        $response = TopUpService::getDataOperators($request->user()->country_code);

        if (isset($response['error'])) {
            return response()->json([
                'message' => __('service_not_available'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($response, ResponseAlias::HTTP_OK);
    }

    public function operatorDataPlans(Request $request)
    {
        $operator = $request->query('operator');
        $response = TopUpService::getOperatorDataPlans($operator);

        if (isset($response['error'])) {
            return response()->json([
                'message' => __('service_not_available'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json($response, ResponseAlias::HTTP_OK);
    }

    public function detectMobileOperator(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'phone' => ['required', 'numeric:'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $response = TopUpService::detectOperator($request->phone, $request->user()->country_code);

        return response()->json([
            'name' => $response['name'],
            'id' => $response['id'],
            'logo_url' => $response['logoUrls'][0],
            'min_amount' => $response['localMinAmount'],
            'max_amount' => $response['localMaxAmount'],
        ]);
    }

    public function airtime(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'phone' => ['required', 'numeric:'],
            'amount' => ['required', 'numeric'],
            'operator' => ['required'],
            'payment_method' => ['required'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        if ($request->payment_method == 'wallet') {
            $transactionPin = TransactionPin::where('user_id', $request->user()->id)->first();

            if (! Hash::check($request->transaction_pin, $transactionPin->pin)) {
                return response()->json([
                    'message' => __('invalid_transaction_pin'),
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            if (! $request->user()->hasCredits($request->amount)) {
                return response()->json([
                    'message' => __('insufficient_balance'),
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $request->user()->creditDeduct($request->amount, 'Topup purchase');
        } else {
            $momoPayment = TouchPayService::collectPayment([
                'email' => $request->user()->email,
                'firstname' => $request->user()->first_name,
                'lastname' => $request->user()->last_name,
                'mobile_number' => $request->payment_phone,
                'otp' => $request->otp ?? '',
                'amount' => $request->amount,
                'provider' => $request->provider,
                'transaction_ref' => now()->timestamp,
            ]);

            if (! $momoPayment['status']) {
                return response()->json([
                    'message' => $momoPayment['message'],
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }
        }

        $response = TopUpService::topup([
            'amount' => $request->amount,
            'operatorId' => $request->operator,
            'recipientPhone' => [
                'number' => $request->phone,
                'countryCode' => $request->user()->country_code,
            ],
            'useLocalAmount' => true,
        ]);

        if (isset($response['error'])) {
            $request->user()->creditAdd($request->amount, 'Topup refund');

            return response()->json([
                'message' => __('service_not_available'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'type' => 'debit',
            'purpose' => 'topup',
            'reference' => 'OWP-TP-'.now()->timestamp,
            'amount' => $request->amount,
            'payment_method' => ucfirst($request->payment_method),
            'description' => "Purchase $request->amount ".$response['operatorName']." to $request->phone",
            'status' => 'processed',
        ]);

        return response()->json([
            'transaction' => $transaction,
        ], ResponseAlias::HTTP_OK);
    }
}
