<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: SetLocaleMiddleware.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 3/29/26
 * Time: 9:50 PM
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->hasSession()
            ? $request->session()->get('locale', config('app.locale'))
            : config('app.locale');

        if (in_array($locale, ['en', 'fr'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
