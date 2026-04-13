<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: ExchangeService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/12/26
 * Time: 8:21 PM
 */

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;

class ExchangeService
{
    const string BASE_URL = 'https://v6.exchangerate-api.com/v6/';

    /**
     * @throws ConnectionException
     */
    public static function rate(string $from, string $to): float|int
    {
        $response = \Http::get(self::BASE_URL.'/'.config('exchange.api_key').'/pair/'.$from.'/'.$to);

        if ($response->ok()) {
            $data = $response->json();

            return (float) $data['conversion_rate'];
        }

        return 0;
    }
}
