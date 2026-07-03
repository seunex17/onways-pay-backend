<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: TopUpService.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 7/1/26
 * Time: 2:05 PM
 */

namespace App\Services;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TopUpService
{
    protected static string $baseUrl = 'https://topups.reloadly.com/';

    protected static string $tokenCacheKey = 'reloadly_topups_access_token';

    protected static function http(): PendingRequest|Factory
    {
        return Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/com.reloadly.topups-v1+json',
            ])
            ->acceptJson()
            ->withToken(static::accessToken());
    }

    protected static function post(string $endpoint, array $query = []): array
    {
        $response = static::http()->post(static::$baseUrl.$endpoint, $query);

        return static::parseResponse($response);
    }

    protected static function get(string $endpoint, array $query = []): array
    {
        $response = static::http()->get(static::$baseUrl.$endpoint, $query);

        return static::parseResponse($response);
    }

    protected static function parseResponse(Response $response): array
    {
        if ($response->failed()) {
            return [
                'error' => true,
                'status' => $response->status(),
                'message' => 'HTTP request failed.',
                'body' => $response->json(),
            ];
        }

        $decoded = $response->json();

        if (is_null($decoded)) {
            return [
                'error' => false,
                'raw' => $response->body(),
            ];
        }

        return $decoded;
    }

    protected static function accessToken(): string
    {
        return Cache::remember(
            static::$tokenCacheKey,
            static::tokenTtl(),
            fn () => static::fetchAccessToken()
        );
    }

    protected static function fetchAccessToken(): string
    {
        $response = Http::acceptJson()
            ->post('https://auth.reloadly.com/oauth/token', [
                'client_id' => config('reloadly.client_id'),
                'client_secret' => config('reloadly.client_secret'),
                'grant_type' => 'client_credentials',
                'audience' => 'https://topups.reloadly.com',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Unable to obtain Reloadly access token: '.$response->body());
        }

        $data = $response->json();

        Cache::put(
            static::$tokenCacheKey.'_expires_at',
            now()->addSeconds($data['expires_in']),
            $data['expires_in']
        );

        return $data['access_token'];
    }

    protected static function tokenTtl(): int
    {
        $buffer = 300;

        return 5184000 - $buffer;
    }

    public static function getBalance(): array
    {
        return static::get('accounts/balance');
    }

    public static function getCountry(string $code): array
    {
        return static::get("countries/$code");
    }

    public static function detectOperator(string $phone, string $country): array
    {
        return static::get("operators/auto-detect/phone/$phone/countries/$country");
    }

    public static function topup(array $data): array
    {
        return static::post('topups', $data);
    }

    public static function getFxRate(int $amount, int $operatorId)
    {
        return static::post('operators/fx-rate', [
            'amount' => $amount,
            'operatorId' => $operatorId,
        ]);
    }
}
