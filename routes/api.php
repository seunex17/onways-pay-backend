<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use Illuminate\Support\Facades\Route;

// Auth Controller
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/resend-email-verification', [ApiAuthController::class, 'resendEmailVerification']);
Route::post('/verify-email', [ApiAuthController::class, 'verifyEmail']);
