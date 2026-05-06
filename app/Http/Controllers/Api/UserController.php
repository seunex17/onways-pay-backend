<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: UserController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/1/26
 * Time: 9:33 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordUpdateMail;
use App\Models\Device;
use App\Models\TransactionPin;
use App\Services\MessagingService;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends Controller
{
    public function checkLogin(Request $request)
    {
        $user = $request->user();

        return response()->json($user, ResponseAlias::HTTP_OK);
    }

    public function walletBalance(Request $request)
    {
        $user = $request->user();

        return response()->json($user->creditBalance(), ResponseAlias::HTTP_OK);
    }

    public function updateProfilePhoto(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors()->first(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        $user->profile_photo = $request->file('image')->store('profile_photos', 'public');
        $user->save();

        return response()->json([
            'message' => __('profile_updated'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function editProfile(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'gender' => ['required', 'string'],
            'date_of_birth' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        $user->update($request->input());

        return response()->json([
            'message' => __('profile_updated'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function updatePassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => ['required', 'string', 'min:8'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => __('old_password_incorrect'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        Mail::to($user->email)->send(new PasswordUpdateMail($user));

        $user->tokens()->delete();

        return response()->json([
            'message' => __('password_updated'),
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    public function sendPinChangeOtp(Request $request)
    {
        $user = $request->user();
        $key = 'resend-sms-otp:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts = 1)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => __('too_many_attempts', ['seconds' => $seconds]),
            ]);
        }

        RateLimiter::hit($key, 60);

        $phone = $user->phone_code.$user->phone;

        $otp = (new Otp)->generate($phone, 'numeric', 6, 5);

        if (! MessagingService::sendSms($phone, __('otp_sms_message', [
            'otp' => $otp->token,
            'appName' => config('app.name'),
            'minute' => '5',
        ]))) {
            return response()->json([
                'message' => __('sms_send_failed'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('sms_sent_successfully'),
        ]);
    }

    public function verifyPinChangeOtp(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'otp' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();

        $phone = $user->phone_code.$user->phone;
        $otp = (new Otp)->validate($phone, $request->otp);

        if (! $otp->status) {
            return response()->json([
                'message' => __('otp_not_verified'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('otp_verified'),
        ], ResponseAlias::HTTP_OK);
    }

    public function changeTransactionPin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'pin' => ['required', 'string', 'min:4', 'max:4'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        TransactionPin::updateOrCreate(['user_id' => $user->id], $request->input());

        return response()->json([
            'message' => __('transaction_pin_updated'),
        ], ResponseAlias::HTTP_OK);
    }

    public function updatePushNotification(Request $request)
    {
        $user = $request->user();
        $user->enable_push_notification = ! $user->enable_push_notification;
        $user->save();

        return response()->json([
            'message' => __('profile_updated'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->account_delete_at = now();
        $user->save();

        $user->tokens()->delete();

        return response()->json([
            'message' => __('account_deleted'),
        ], ResponseAlias::HTTP_OK);
    }

    public function addDevice(Request $request)
    {
        $user = $request->user();
        $device = Device::updateOrCreate(['user_id' => $user->id], $request->input());
    }
}
