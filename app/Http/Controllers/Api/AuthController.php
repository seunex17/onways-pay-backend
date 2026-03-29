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
}
