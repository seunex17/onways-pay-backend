<?php

namespace App\Services;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GiftCardService
{
    protected static string $baseUrl = 'https://giftcards.reloadly.com/';

    protected static string $tokenCacheKey = 'reloadly_giftcards_access_token';

    protected static function http(): PendingRequest|Factory
    {
        return Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/com.reloadly.giftcards-v1+json',
            ])
            ->acceptJson()
            ->withToken(static::accessToken());
    }

    protected static function get(string $endpoint, array $query = []): array
    {
        $response = static::http()->get(static::$baseUrl.$endpoint, $query);

        return static::parseResponse($response);
    }

    protected static function post(string $endpoint, array $data = []): array
    {
        $response = static::http()->post(static::$baseUrl.$endpoint, $data);

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
                'audience' => 'https://giftcards.reloadly.com',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Unable to obtain Reloadly gift card access token: '.$response->body());
        }

        $data = $response->json();

        return $data['access_token'];
    }

    protected static function tokenTtl(): int
    {
        $buffer = 300;

        return 5184000 - $buffer;
    }

    public static function countries(): array
    {
        return static::get('countries');
    }

    public static function products(array $query = []): array
    {
        return static::get('products', array_filter($query, fn (mixed $value): bool => filled($value)));
    }

    public static function product(string $productId): array
    {
        return static::get("products/$productId");
    }

    public static function redeemInstructions(string $productId): array
    {
        return static::get("products/$productId/redeem-instructions");
    }

    public static function order(array $data): array
    {
        return static::post('orders', $data);
    }

    public static function fxRates(string $currencyCode, float $amount): array
    {
        return static::get('fx-rate', [
            'currencyCode' => $currencyCode,
            'amount' => $amount,
        ]);
    }
}
