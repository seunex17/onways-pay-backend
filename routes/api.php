<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [ApiAuthController::class, 'register']);
