<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: MonoRecipient.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 8/10/26
 * Time: 12:09 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonoRecipient extends Model
{
    protected $fillable = [
        'phone_number',
        'country_code',
        'phone_code',
        'provider',
        'last_used_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'timestamp',
        ];
    }
}
