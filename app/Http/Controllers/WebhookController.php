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
use App\Models\Exchange;
use App\Models\ExchangePayout;
use App\Models\FuelVoucherPurchase;
use App\Models\MomoPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\WithdrawPayout;
use App\Services\FuelVoucherService;
use App\Services\NotificationService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleOxapay(Request $request)
    {
        $data = $request->all();
        $trackId = $data['track_id'];

        $cryptoPayment = CryptoPayment::where('track_id', $trackId)->first();

        if ($cryptoPayment) {
            if ($data['type'] === 'payout') {
                $this->oxapayPayout($data, $cryptoPayment);
            } else {
                $this->oxapayPayin($data, $cryptoPayment);
            }
        }
    }

    protected function oxapayPayin(array $data, CryptoPayment $cryptoPayment)
    {
        $transaction = Transaction::find($cryptoPayment->transaction_id);

        switch (strtolower($data['status'])) {
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

                TransactionService::process($transaction);

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

    protected function oxapayPayout(array $data, CryptoPayment $cryptoPayment)
    {
        $transaction = Transaction::find($cryptoPayment->transaction_id);

        switch (strtolower($data['status'])) {
            case 'confirming':
                $cryptoPayment->status = $data['status'];
                $cryptoPayment->save();

                $transaction->status = 'processing';
                $transaction->save();

                broadcast(new TransactionStatusEvent($transaction));

                break;
            case 'confirmed':
                $cryptoPayment->status = $data['status'];
                $cryptoPayment->save();

                $user = User::find($transaction->user_id);
                $user->creditAdd($transaction->amount, 'Wallet Deposit');

                $transaction->status = 'processed';
                $transaction->save();

                broadcast(new TransactionStatusEvent($transaction));

                // TODO - Send Push notification

                break;
            case 'failed':
                $cryptoPayment->status = $data['status'];
                $cryptoPayment->save();

                $transaction->status = 'expired';
                $transaction->save();

                broadcast(new TransactionStatusEvent($transaction));

                break;
        }
    }

    public function handleTouchpay(Request $request, FuelVoucherService $fuelVoucherService)
    {
        $data = $request->all();
        $ref = $data['partner_transaction_id'];
        $transaction = Transaction::with('user')
            ->where('reference', $ref)->first();

        if (! $transaction) {
            return response()->noContent();
        }

        if ($data['status'] === 'FAILED') {
            $transaction->status = 'canceled';
            $transaction->save();
            $transaction->fuelVoucherPurchase?->update(['status' => 'cancelled']);
            broadcast(new TransactionStatusEvent($transaction));
        }

        if ($data['status'] === 'SUCCESSFUL') {
            $momoPayment = MomoPayment::where('transaction_id', $transaction->id)->first();
            if ($momoPayment) {
                $momoPayment->status = $data['status'];
                $momoPayment->save();
            }

            if ($transaction->purpose === 'fuel_voucher') {
                $purchase = FuelVoucherPurchase::query()->where('transaction_id', $transaction->id)->firstOrFail();
                $fuelVoucherService->complete($purchase);
            } else {
                TransactionService::process($transaction);
            }
        }
    }

    public function handleOxapayExchange(Request $request)
    {
        $data = $request->all();
        $trackId = $data['track_id'];

        $status = strtolower($data['status']);

        $exchangePayout = ExchangePayout::with('exchange')
            ->where([
                'payment_reference' => $data['track_id'],
            ])->first();

        if ($status === 'confirmed') {
            if ($exchangePayout instanceof ExchangePayout && $exchangePayout->exchange instanceof Exchange) {
                $exchangePayout->exchange->status = 'completed';
                $exchangePayout->exchange->save();

                $transaction = Transaction::find($exchangePayout->exchange->transaction_id);
                if ($transaction instanceof Transaction) {
                    $transaction->status = 'processed';
                    $transaction->save();
                }

                $exchangePayout->status = 'complete';
                $exchangePayout->save();

                if ($transaction instanceof Transaction) {
                    $user = User::find($transaction->user_id);
                    if ($user instanceof User) {
                        NotificationService::sendPushNotification($user, [
                            'title' => __('exchange_processed'),
                            'body' => __('exchange_process_info', [
                                'fromCurrency' => $exchangePayout->exchange->from_currency,
                                'toCurrency' => $exchangePayout->exchange->to_currency,
                            ]),
                            'type' => 'notification',
                        ]);
                    }
                }
            }

            return;
        }

        if ($exchangePayout instanceof ExchangePayout) {
            if ($exchangePayout->exchange instanceof Exchange) {
                $exchangePayout->exchange->status = 'hold';
                $exchangePayout->exchange->save();
            }

            $exchangePayout->increment('tries');
            $exchangePayout->uuid = \Str::uuid();
            $exchangePayout->next_retry = now()->addHours($exchangePayout->tries);
            $exchangePayout->status = 'failed';
            $exchangePayout->save();

            // TODO - SEND FAIL TRANSACTION EMAIL TO ALL ADMIN
        }
    }

    public function handleOxapayWithdraw(Request $request)
    {
        $data = $request->all();
        $trackId = $data['track_id'];

        $status = strtolower($data['status']);

        $withdrawPayout = WithdrawPayout::with('withdrawal')
            ->where([
                'payment_reference' => $data['track_id'],
            ])->first();

        if ($status === 'confirmed') {
            if ($withdrawPayout instanceof WithdrawPayout && $withdrawPayout->withdrawal instanceof Withdrawal) {
                $withdrawPayout->withdrawal->status = 'success';
                $withdrawPayout->withdrawal->save();

                $transaction = Transaction::find($withdrawPayout->withdrawal->transaction_id);
                if ($transaction instanceof Transaction) {
                    $transaction->status = 'processed';
                    $transaction->save();

                    broadcast(new TransactionStatusEvent($transaction));
                }

                $withdrawPayout->status = 'complete';
                $withdrawPayout->save();

                if ($transaction instanceof Transaction) {
                    $user = User::find($transaction->user_id);
                    if ($user instanceof User) {
                        NotificationService::sendPushNotification($user, [
                            'title' => __('withdraw_processed'),
                            'body' => __('withdraw_processed_info', [
                                'amount' => number_format($withdrawPayout->withdrawal->amount, 0),
                                'currency' => $withdrawPayout->withdrawal->currency,
                                'method' => $withdrawPayout->withdrawal->method,
                            ]),
                            'type' => 'notification',
                        ]);
                    }
                }
            }

            return;
        }

        if ($withdrawPayout instanceof WithdrawPayout) {
            if ($withdrawPayout->withdrawal instanceof Withdrawal) {
                $withdrawPayout->withdrawal->status = 'hold';
                $withdrawPayout->withdrawal->save();
            }

            $withdrawPayout->increment('tries');
            $withdrawPayout->uuid = \Str::uuid();
            $withdrawPayout->next_retry = now()->addHours($withdrawPayout->tries);
            $withdrawPayout->status = 'failed';
            $withdrawPayout->save();

            // TODO - SEND FAIL TRANSACTION EMAIL TO ALL ADMIN
        }
    }
}
