<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: MessagingService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 3/31/26
 * Time: 10:16 PM
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MessagingService
{
    public function __construct() {}

    public static function sendSms(string $to, string $message)
    {
        try {
            $response = Http::post('https://api-public-2.mtarget.fr/messages', [
                'username' => config('mtarget.username'),
                'password' => config('mtarget.password'),
                'msisdn' => '+'.$to,
                'msg' => $message,
                'serviceid' => config('mtarget.serviceid'),
                'sender' => config('mtarget.sender'),
            ])->json();

            dd($response);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
