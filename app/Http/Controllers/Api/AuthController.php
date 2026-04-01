<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: AuthController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 3/29/26
 * Time: 7:23 AM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\TransactionPin;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    /**
     * @throws \Exception
     */
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $input = $request->input();
        $input['name'] = $input['first_name'].' '.$input['last_name'];
        $input['uuid'] = Str::uuid();

        $user = User::create($input);
        $user->customer_id = str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
        $user->save();
        $top = (new Otp)->generate($user->email, 'numeric', 6, 10);

        Mail::to($user->email)->send(new VerifyEmailMail($top->token));

        return response()->json([
            'message' => __('registration_success'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    public function resendEmailVerification(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => __('email_not_found'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $otp = (new Otp)->generate($user->email, 'numeric', 6, 10);
        Mail::to($user->email)->send(new VerifyEmailMail($otp->token));

        return response()->json([
            'message' => __('new_email_otp_sent'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => __('email_not_found'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $otp = (new Otp)->validate($user->email, $request->token);

        if (! $otp->status) {
            return response()->json([
                'message' => __('invalid_email_otp'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => __('email_verified'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    public function sendPhoneVerification(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => __('email_not_found'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        if (User::where([
            'phone_code' => $user->phone_code,
            'phone' => $request->phone,
        ])->where('email', '!=', $user->email)
            ->exists()) {
            return response()->json([
                'message' => __('phone_already_verified'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->phone = $request->phone;
        $user->save();

        $phoneNumber = $user->phone_code.$request->phone;
        $otp = (new Otp)->generate($phoneNumber, 'numeric', 6, 10);

        // TODO - Send sms otp to user

        return response()->json([
            'message' => __('phone_otp_sent'),
            'user' => $user,
        ]);
    }

    public function verifyPhone(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => __('email_not_found'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $otp = (new Otp)->validate($user->phone_code.$user->phone, $request->token);

        if (! $otp->status) {
            return response()->json([
                'message' => __('invalid_phone_otp'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'message' => __('phone_verified'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function setTransactionPin(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => __('email_not_found'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        TransactionPin::updateOrCreate(['user_id' => $user->id], $request->input());

        Mail::to($user->email)->send(new WelcomeMail($user));

        return response()->json([
            'message' => __('transaction_pin_updated'),
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }
}
