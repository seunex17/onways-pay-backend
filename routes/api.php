<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Auth Controller
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/resend-email-verification', [ApiAuthController::class, 'resendEmailVerification']);
Route::post('/verify-email', [ApiAuthController::class, 'verifyEmail']);
Route::post('/send-phone-verification', [ApiAuthController::class, 'sendPhoneVerification']);
Route::post('/verify-phone', [ApiAuthController::class, 'verifyPhone']);
Route::post('/set-transaction-pin', [ApiAuthController::class, 'setTransactionPin']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    // User Controller
    Route::get('/check-login', [UserController::class, 'checkLogin']);
    Route::get('/wallet-balance', [UserController::class, 'walletBalance']);

    // Transaction Controller
    Route::post('/deposit', [TransactionController::class, 'deposit']);
    Route::post('/prepare-exchange', [TransactionController::class, 'prepareExchange']);
    Route::post('/exchange', [TransactionController::class, 'exchange']);

    // Payment Controller
    Route::prefix('/payment')->group(function () {
        Route::post('/request', [PaymentController::class, 'request']);
    });
});
