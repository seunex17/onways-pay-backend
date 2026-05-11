<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: NotificationService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 5/6/26
 * Time: 9:58 AM
 */

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationService
{
    public function __construct()
    {
        //
    }

    public static function sendPushNotification(User $user, array $data): void
    {
        $data['id'] = Str::uuid()->toString();
        $device = Device::where('user_id', $user->id)->first();

        if ($device) {
            try {
                $message = CloudMessage::new()
                    ->withData($data)
                    ->withHighestPossiblePriority()
                    ->withToken($device->token);
                app('firebase.messaging')->send($message);
            } catch (MessagingException $e) {
                Log::info($e->getMessage());
            }
        }
    }
}
