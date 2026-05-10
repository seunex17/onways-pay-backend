<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: TransactionService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 5/10/26
 * Time: 2:44 AM
 */

namespace App\Services;

use App\Events\TransactionStatusEvent;
use App\Models\Deposit;
use App\Models\Exchange;
use App\Models\ExchangePayout;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawPayout;

class TransactionService
{
    public function __construct() {}

    public static function process(Transaction $transaction): void
    {
        switch ($transaction->purpose) {
            case 'deposit':
                self::deposit($transaction);
                break;
            case 'exchange':
                self::prepareExchange($transaction);
                break;
            case 'withdraw':
                self::prepareWithdraw($transaction);
                break;
            default:
                //

        }
    }

    protected static function deposit(Transaction $transaction): void
    {
        $deposit = Deposit::where('transaction_id', $transaction->id)
            ->with('user')
            ->first();

        $deposit?->user->creditAdd($deposit->amount, 'Deposit');

        $transaction->status = 'processed';
        $transaction->save();

        broadcast(new TransactionStatusEvent($transaction));

        NotificationService::sendPushNotification($deposit->user, [
            'title' => __('wallet_deposited'),
            'body' => __('wallet_deposited_info', [
                'amount' => number_format($deposit->amount, 2, '.', ','),
                'appName' => config('app.name'),
            ]),
            'type' => 'alert',
        ]);
    }

    protected static function prepareExchange(Transaction $transaction): void
    {
        $exchange = Exchange::where('transaction_id', $transaction->id)
            ->with('user')
            ->first();
        $uuid = \Str::uuid()->toString();

        if ($exchange) {
            $cryptoPayment = CryptoPaymentService::exchangePayout(
                $exchange->amount_received,
                $uuid,
                $exchange->to_source,
                $exchange->to_currency,
            );

            if ($cryptoPayment['status'] && $cryptoPayment['data'] !== null) {
                $transaction->status = 'processing';
                $transaction->save();

                broadcast(new TransactionStatusEvent($transaction));

                $exchangePayout = ExchangePayout::create([
                    'exchange_id' => $exchange->id,
                    'uuid' => $uuid,
                    'payment_reference' => $cryptoPayment['track_id'],
                ]);
            }
        }
    }

    protected static function prepareWithdraw(Transaction $transaction)
    {
        $withdrawal = Withdrawal::where('transaction_id', $transaction->id)
            ->with('user')
            ->first();
        $uuid = \Str::uuid()->toString();

        if ($withdrawal) {
            $cryptoPayment = CryptoPaymentService::withdrawalPayout(
                $withdrawal->amount = -$withdrawal->fee,
                $uuid,
                $withdrawal->destination,
                $withdrawal->currency,
            );

            if ($cryptoPayment['status'] && $cryptoPayment['data'] !== null) {
                $transaction->status = 'processing';
                $transaction->save();

                broadcast(new TransactionStatusEvent($transaction));

                WithdrawPayout::create([
                    'withdrawal_id' => $withdrawal->id,
                    'uuid' => $uuid,
                    'payment_reference' => $cryptoPayment['track_id'],
                ]);
            }
        }
    }
}
