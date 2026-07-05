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
            \Log::info($response->json());

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

    public static function getFxRate(int $amount, int $operatorId): array
    {
        return static::post('operators/fx-rate', [
            'amount' => $amount,
            'operatorId' => $operatorId,
        ]);
    }

    public static function getOperatorById(string $operatorId): array
    {
        return static::get("operators/$operatorId");
    }

    public static function getOperators(string $countryCode): array
    {
        return static::get("operators/countries/$countryCode", [
            'includeData' => true,
        ]);
    }

    public static function getDataOperators(string $countryCode): array
    {
        $response = static::getOperators($countryCode);

        if (isset($response['error'])) {
            return $response;
        }

        return array_values(array_filter(array_map(
            fn (array $operator): ?array => static::formatDataOperatorSummary($operator),
            static::operatorsFromResponse($response),
        )));
    }

    public static function getDataPlans(string $countryCode): array
    {
        $response = static::getOperators($countryCode);

        if (isset($response['error'])) {
            return $response;
        }

        return [
            'country_code' => strtoupper($countryCode),
            'operators' => array_values(array_filter(array_map(
                fn (array $operator): ?array => static::formatDataOperator($operator),
                static::operatorsFromResponse($response),
            ))),
        ];
    }

    public static function getOperatorDataPlans(string $operatorId): array
    {
        $response = static::getOperatorById($operatorId);

        if (isset($response['error'])) {
            return $response;
        }

        if (! (bool) ($response['data'] ?? false)) {
            return [];
        }

        return static::formatOperatorDataPlans($response);
    }

    protected static function operatorsFromResponse(array $response): array
    {
        if (isset($response['content']) && is_array($response['content'])) {
            return $response['content'];
        }

        if (array_is_list($response)) {
            return $response;
        }

        return [];
    }

    protected static function formatDataOperator(array $operator): ?array
    {
        if (! (bool) ($operator['data'] ?? false)) {
            return null;
        }

        $plans = static::formatPlans(
            $operator['localFixedAmounts'] ?? [],
            $operator['localFixedAmountsDescriptions'] ?? [],
        );

        return [
            'id' => $operator['operatorId'] ?? $operator['id'] ?? null,
            'name' => $operator['name'] ?? null,
            'logo_url' => $operator['logoUrls'][0] ?? null,
            'min_amount' => $operator['localMinAmount'] ?? null,
            'max_amount' => $operator['localMaxAmount'] ?? null,
            'plans' => $plans,
        ];
    }

    protected static function formatDataOperatorSummary(array $operator): ?array
    {
        if (! (bool) ($operator['data'] ?? false)) {
            return null;
        }

        return [
            'name' => $operator['name'] ?? null,
            'id' => $operator['operatorId'] ?? $operator['id'] ?? null,
            'logo_url' => $operator['logoUrls'][0] ?? null,
        ];
    }

    protected static function formatOperatorDataPlans(array $operator): array
    {
        $operatorId = (string) ($operator['operatorId'] ?? $operator['id'] ?? '');
        $operatorName = (string) ($operator['name'] ?? 'Data plan');
        $amounts = $operator['localFixedAmounts'] ?? [];
        $descriptions = $operator['localFixedAmountsDescriptions'] ?? [];

        if (! is_array($amounts)) {
            return [];
        }

        if (! is_array($descriptions)) {
            $descriptions = [];
        }

        return array_map(
            fn (int|float|string $amount): array => static::formatOperatorDataPlan(
                $operatorId,
                $operatorName,
                $amount,
                static::descriptionForAmount($descriptions, $amount),
            ),
            array_values($amounts),
        );
    }

    protected static function formatOperatorDataPlan(
        string $operatorId,
        string $operatorName,
        int|float|string $amount,
        ?string $description,
    ): array {
        $parsedDescription = static::parsePlanDescription($description);
        $price = (float) $amount;

        return [
            'id' => $operatorId.'-'.$amount,
            'name' => $description ?: $operatorName.' '.number_format($price, 2, '.', ''),
            'price' => $price,
            'dataAmount' => $parsedDescription['dataAmount'],
            'validity' => $parsedDescription['validity'],
            'description' => $description,
        ];
    }

    protected static function parsePlanDescription(?string $description): array
    {
        if (! $description) {
            return [
                'dataAmount' => null,
                'validity' => null,
            ];
        }

        $parts = preg_split('/\s+-\s+/', $description, 2);

        return [
            'dataAmount' => $parts[0] ?? null,
            'validity' => $parts[1] ?? null,
        ];
    }

    protected static function formatPlans(array $amounts, array $descriptions): array
    {
        return array_map(
            fn (int|float|string $amount): array => [
                'amount' => $amount,
                'description' => static::descriptionForAmount($descriptions, $amount),
            ],
            array_values($amounts),
        );
    }

    protected static function descriptionForAmount(array $descriptions, int|float|string $amount): ?string
    {
        $keys = [
            (string) $amount,
            number_format((float) $amount, 2, '.', ''),
            number_format((float) $amount, 0, '.', ''),
        ];

        foreach ($keys as $key) {
            if (isset($descriptions[$key]) && is_string($descriptions[$key])) {
                return $descriptions[$key];
            }
        }

        return null;
    }
}
