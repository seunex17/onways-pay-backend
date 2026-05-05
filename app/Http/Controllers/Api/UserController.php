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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
                'message' => $validate->errors()->first()
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
                'message' => $validate->errors()->first()
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
        $user = $request->user();
        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => __('old_password_incorrect'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->password  = bcrypt($request->password);
        $user->save();

        Mail::to($user->email)->send(new PasswordUpdateMail($user));

        $user->tokens()->delete();

        return response()->json([
            'message' => __('password_updated'),
        ], ResponseAlias::HTTP_OK);
    }
}
