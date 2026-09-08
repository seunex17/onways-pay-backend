<?php

use App\Events\TransactionStatusEvent;
use App\Http\Controllers\WebhookController;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CryptoPaymentService;
use App\Services\GiftCardService;
use App\Services\NotificationService;
use App\Services\TouchPayService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');
Route::get('/services', fn () => Inertia::render('Services'))->name('services');
Route::get('/how-it-works', fn () => Inertia::render('HowItWorks'))->name('how-it-works');
Route::get('/security', fn () => Inertia::render('Security'))->name('security');
Route::get('/faq', fn () => Inertia::render('Faq'))->name('faq');
Route::get('/contact', fn () => Inertia::render('Contact'))->name('contact');
Route::post('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'fr'], true), 404);
    session(['locale' => $locale]);

    return back();
})->name('language.update');

Route::get('/test', function () {
    // return GiftCardService::fxRates('XOF', 1000);

    //    $user = User::find(1);
    //    $user->creditAdd(100000);
    //    NotificationService::sendPushNotification($user, [
    //        'title' => 'hello world',
    //        'body' => 'hello world',
    //    ]);

    // $res = CryptoPaymentService::whiteLabel(20, time(), 'BNB');
    //    $res = CryptoPaymentService::payout(
    //        8,
    //        time(),
    //        '0xde5833959aee02b55c8bd44c403d153d44454fdb',
    //        'BNB');

    // $res = CryptoPaymentService::swap('USDT', 'BNB', 10);

    // $res = CryptoPaymentService::getBalance();
    // dd($res);
    // TransactionStatusEvent::dispatch(Transaction::latest()->first());
    //    $data = [
    //        'transaction_ref' => time(),
    //        'email' => 'jhon@mail.com',
    //        'firstname' => 'Jhon',
    //        'lastname' => 'Doe',
    //        'amount' => 10,
    //        'mobile_number' => '0594124241',
    //        'provider' => 'mtn',
    //    ];

    // $res = TouchPayService::collectPayment($data);
    // $res = TouchPayService::checkBalance();
    // $res = TouchPayService::sendMoney($data);
});

Route::name('webhook.')->prefix('webhook')->group(function () {
    Route::post('/oxapay', [WebhookController::class, 'handleOxapay'])->name('oxapay');
    Route::post('/touchpay', [WebhookController::class, 'handleTouchpay'])->name('touchpay');
    Route::post('/oxapay-exchange', [WebhookController::class, 'handleOxapayExchange'])->name('oxapay-exchange');
    Route::post('/oxapay-withdraw', [WebhookController::class, 'handleOxapayWithdraw'])->name('oxapay-withdraw');
});
