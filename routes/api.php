<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\FuelVoucherController;
use App\Http\Controllers\Api\GiftCardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Partner\FuelVoucherRedemptionController;
use Illuminate\Support\Facades\Route;

// Auth Controller
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/resend-email-verification', [ApiAuthController::class, 'resendEmailVerification']);
Route::post('/verify-email', [ApiAuthController::class, 'verifyEmail']);
Route::post('/send-phone-verification', [ApiAuthController::class, 'sendPhoneVerification']);
Route::post('/verify-phone', [ApiAuthController::class, 'verifyPhone']);
Route::post('/set-transaction-pin', [ApiAuthController::class, 'setTransactionPin']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/forget-password', [ApiAuthController::class, 'forgetPassword']);

Route::post('/partner/fuel-vouchers/redeem', FuelVoucherRedemptionController::class)
    ->middleware('throttle:60,1')->name('fuel-vouchers.redeem');

Route::group(['middleware' => ['auth:sanctum']], function () {
    // User Controller
    Route::get('/check-login', [UserController::class, 'checkLogin']);
    Route::get('/wallet-balance', [UserController::class, 'walletBalance']);
    Route::get('/notifications', [UserController::class, 'notifications']);
    Route::get('/unread-notifications', [UserController::class, 'unreadNotifications']);

    Route::put('/mark-notification-read', [UserController::class, 'markNotificationRead']);

    // Transaction Controller
    Route::get('/transactions', [TransactionController::class, 'transactions']);
    Route::get('/recent-transactions', [TransactionController::class, 'recentTransactions']);

    Route::post('/deposit', [TransactionController::class, 'deposit']);
    Route::post('/prepare-exchange', [TransactionController::class, 'prepareExchange']);
    Route::post('/exchange', [TransactionController::class, 'exchange']);

    // Payment Controller
    Route::prefix('/payment')->group(function () {
        Route::get('/validate-customer-id', [PaymentController::class, 'validateCustomerId']);

        Route::post('/request', [PaymentController::class, 'request']);
        Route::post('/withdraw', [PaymentController::class, 'withdraw']);
        Route::post('/scan-code', [PaymentController::class, 'scanCode']);
        Route::post('/make-payment', [PaymentController::class, 'makePayment']);
        Route::post('/transfer', [PaymentController::class, 'transfer']);
    });

    // User Controller
    Route::prefix('/account')->group(function () {
        Route::post('/update-profile-photo', [UserController::class, 'updateProfilePhoto']);
        Route::post('/send-pin-change-otp', [UserController::class, 'sendPinChangeOtp']);
        Route::post('/verify-pin-change-otp', [UserController::class, 'verifyPinChangeOtp']);
        Route::post('/add-device', [UserController::class, 'addDevice']);

        Route::put('/edit-profile', [UserController::class, 'editProfile']);
        Route::put('/update-password', [UserController::class, 'updatePassword']);
        Route::put('/change-transaction-pin', [UserController::class, 'changeTransactionPin']);
        Route::put('/update-push-notification', [UserController::class, 'updatePushNotification']);

        Route::delete('/delete-account', [UserController::class, 'deleteAccount']);
    });

    // Topup Controller
    Route::prefix('/topup')->group(function () {
        Route::get('/operators', [TopupController::class, 'operators']);
        Route::get('/data-plans', [TopupController::class, 'operatorDataPlans']);

        Route::post('/detect-mobile-operator', [TopupController::class, 'detectMobileOperator']);
        Route::post('/airtime', [TopupController::class, 'airtime']);
    });

    // Gift Card Controller
    Route::prefix('/gift-cards')->group(function () {
        Route::get('/countries', [GiftCardController::class, 'countries']);
        Route::get('/products', [GiftCardController::class, 'products']);
        Route::get('/products/{product}', [GiftCardController::class, 'product']);
        Route::get('/products/{product}/redeem-instructions', [GiftCardController::class, 'redeemInstructions']);
        Route::get('/orders', [GiftCardController::class, 'orders']);
        Route::get('/orders/{giftCardOrder}', [GiftCardController::class, 'order']);

        Route::post('/purchase', [GiftCardController::class, 'purchase']);
    });

    Route::prefix('/fuel-vouchers')->middleware('throttle:60,1')->group(function () {
        Route::post('/purchase', [FuelVoucherController::class, 'purchase'])->middleware('throttle:10,1');
        Route::get('/purchases/{transaction}', [FuelVoucherController::class, 'status']);
        Route::get('/', [FuelVoucherController::class, 'index']);
        Route::get('/{voucher}', [FuelVoucherController::class, 'show']);
        Route::get('/{voucher}/download', [FuelVoucherController::class, 'download'])->name('fuel-vouchers.download');
    });
});
