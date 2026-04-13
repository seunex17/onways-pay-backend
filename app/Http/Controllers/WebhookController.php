<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: WebhookController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/4/26
 * Time: 2:01 PM
 */

namespace App\Http\Controllers;

use App\Events\TransactionStatusEvent;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleOxapay(Request $request)
    {
        $data = $request->all();
        $trackId = $data['track_id'];

        $cryptoPayment = CryptoPayment::where('track_id', $trackId)->first();

        if ($cryptoPayment) {
            $transaction = Transaction::find($cryptoPayment->transaction_id);

            switch ($data['status']) {
                case 'underpaid':
                case 'paying':
                    $cryptoPayment->status = $data['status'];
                    $cryptoPayment->save();

                    $transaction->status = 'processing';
                    $transaction->save();

                    broadcast(new TransactionStatusEvent($transaction));

                    break;
                case 'paid':
                    $cryptoPayment->status = $data['status'];
                    $cryptoPayment->save();

                    $user = User::find($transaction->user_id);
                    $user->creditAdd($transaction->amount, 'Wallet Deposit');

                    $transaction->status = 'processed';
                    $transaction->save();

                    broadcast(new TransactionStatusEvent($transaction));

                    // TODO - Send Push notification

                    break;
                case 'expired':
                    $cryptoPayment->status = $data['status'];
                    $cryptoPayment->save();

                    $transaction->status = 'expired';
                    $transaction->save();

                    broadcast(new TransactionStatusEvent($transaction));

                    break;
            }
        }
        //        \Log::info(json_encode($data, JSON_PRETTY_PRINT));
    }
}
