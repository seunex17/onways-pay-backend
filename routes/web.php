<?php

use App\Events\TransactionStatusEvent;
use App\Http\Controllers\WebhookController;
use App\Models\Transaction;
use App\Services\CryptoPaymentService;
use App\Services\MessagingService;
use App\Services\TouchPayService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    // MessagingService::sendSms('2250709586293', 'From Zubdev');
    // $res = CryptoPaymentService::whiteLabel(5, time(), 'USDT');
    // dd($res);
    // TransactionStatusEvent::dispatch(Transaction::latest()->first());
    $data = [
        'transaction_ref' => time(),
        'email' => 'jhon@mail.com',
        'firstname' => 'Jhon',
        'lastname' => 'Doe',
        'amount' => 10,
        'mobile_number' => '0594124241',
        'provider' => 'mtn',
    ];

    $res = TouchPayService::collectPayment($data);
    dd($res);
});

Route::name('webhook.')->prefix('webhook')->group(function () {
    Route::post('/oxapay', [WebhookController::class, 'handleOxapay'])->name('oxapay');
    Route::post('/touchpay', [WebhookController::class, 'handleTouchpay'])->name('touchpay');
});
