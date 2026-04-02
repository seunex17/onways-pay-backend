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
}
