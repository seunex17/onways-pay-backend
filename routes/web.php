<?php

use App\Events\TransactionStatusEvent;
use App\Http\Controllers\WebhookController;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CryptoPaymentService;
use App\Services\MessagingService;
use App\Services\TouchPayService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    // MessagingService::sendSms('2250709586293', 'From Zubdev');
    // $res = CryptoPaymentService::whiteLabel(20, time(), 'BNB');
    $user = User::find(4);
    $user->creditAdd(1000, 'Deposit');
    //    $res = CryptoPaymentService::payout(
    //        8,
    //        time(),
    //        '0xde5833959aee02b55c8bd44c403d153d44454fdb',
    //        'BNB');

    // $res = CryptoPaymentService::swap('USDT', 'BNB', 10);

    // $res = CryptoPaymentService::getBalance();
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

    // $res = TouchPayService::collectPayment($data);
    // $res = TouchPayService::checkBalance();
    // $res = TouchPayService::sendMoney($data);

    // dd($res);
});

Route::name('webhook.')->prefix('webhook')->group(function () {
    Route::post('/oxapay', [WebhookController::class, 'handleOxapay'])->name('oxapay');
    Route::post('/touchpay', [WebhookController::class, 'handleTouchpay'])->name('touchpay');
});
