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
use Illuminate\Http\Request;
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
}
