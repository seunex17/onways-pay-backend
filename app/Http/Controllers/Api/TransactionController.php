<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: TransactionController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/3/26
 * Time: 3:28 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Models\MomoPayment;
use App\Models\Transaction;
use App\Services\CryptoPaymentService;
use App\Services\ExchangeService;
use App\Services\TouchPayService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TransactionController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function deposit(Request $request)
    {
        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'type' => 'credit',
            'purpose' => 'deposit',
            'reference' => 'Deposit-'.time(),
            'amount' => $request->amount,
        ]);

        switch ($request->mode) {
            case 'crypto':
                return $this->cryptoDeposit($transaction, $request);
            case 'momo':
                return $this->mobileMoneyDeposit($transaction, $request);

            default:
                return response()->json([], ResponseAlias::HTTP_BAD_REQUEST);

        }
    }

    /**
     * @throws ConnectionException
     */
    public function cryptoDeposit(Transaction $transaction, Request $request)
    {
        $rate = ExchangeService::rate('USD', config('exchange.currency'));
        if ($rate <= 0) {
            $transaction->status = 'canceled';
            $transaction->save();

            return response()->json([
                'message' => 'Invalid rate',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $cryptoRes = CryptoPaymentService::whiteLabel($transaction->amount, $transaction->reference, ucwords($request->currency));

        if (! $cryptoRes['status']) {
            $transaction->status = 'canceled';
            $transaction->description = $cryptoRes['message'];
            $transaction->save();

            return response()->json([
                'message' => $cryptoRes['message'],
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $data = $cryptoRes['data'];
        $payment = CryptoPayment::create([
            'transaction_id' => $transaction->id,
            'track_id' => $data['track_id'],
            'amount' => $data['amount'],
            'currency' => $data['pay_currency'],
            'network' => $data['pay_network'] ?? null,
            'address' => $data['address'],
            'qr_code' => $data['qr_code'] ?? null,
        ]);

        $transaction->amount = $rate * $transaction->amount;
        $transaction->payment_method = 'crypto';
        $transaction->description = __('deposit_to_wallet');
        $transaction->save();

        return response()->json([
            'payment' => $payment,
            'transaction' => Transaction::find($transaction->id),
        ], ResponseAlias::HTTP_CREATED);
    }

    public function mobileMoneyDeposit(Transaction $transaction, Request $request)
    {
        $momoPayment = TouchPayService::collectPayment([
            'email' => $request->user()->email,
            'firstname' => $request->user()->first_name,
            'lastname' => $request->user()->last_name,
            'mobile_number' => $request->phone,
            'otp' => $request->otp ?? '',
            'amount' => $request->amount,
            'provider' => $request->provider,
            'transaction_ref' => $transaction->reference,
        ]);

        if (! $momoPayment['status']) {
            $transaction->status = 'canceled';
            $transaction->description = $momoPayment['message'];
            $transaction->save();

            return response()->json([
                'message' => $momoPayment['message'],
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $data = $momoPayment['data'];
        $payment = MomoPayment::create([
            'transaction_id' => $transaction->id,
            'reference' => $data['idFromClient'],
            'amount' => $data['amount'],
            'fee' => $data['fees'],
            'service' => ucwords($request->provider),
            'service_code' => $data['serviceCode'],
            'recipient_number' => $data['recipientNumber'],
            'status' => $data['status'],
        ]);

        return response()->json([
            'payment' => $payment,
            'transaction' => Transaction::find($transaction->id),
        ]);
    }
}
