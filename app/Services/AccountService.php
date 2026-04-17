<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: AccountService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/17/26
 * Time: 1:06 PM
 */

namespace App\Services;

use App\Models\TransactionPin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccountService
{
    public static function verifyPin(User $user, string $pin): bool
    {
        $transactionPin = TransactionPin::where('user_id', $user->id)->first();

        if (! $transactionPin) {
            return false;
        }

        return Hash::check($pin, $transactionPin->pin);
    }
}
