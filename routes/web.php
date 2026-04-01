<?php

use App\Services\MessagingService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    MessagingService::sendSms('2250709586293', 'From Zubdev');
});
