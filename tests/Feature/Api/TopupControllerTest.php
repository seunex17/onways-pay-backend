<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it lists operators for the authenticated users country code', function () {
    Cache::flush();

    $user = User::factory()->create([
        'country_code' => 'CI',
    ]);

    Sanctum::actingAs($user);

    Http::preventStrayRequests();
    Http::fake([
        'https://auth.reloadly.com/oauth/token' => Http::response([
            'access_token' => 'reloadly-token',
            'expires_in' => 3600,
        ]),
        'https://topups.reloadly.com/operators/countries/CI*' => Http::response([
            [
                'operatorId' => 253,
                'name' => 'Orange CI',
                'data' => true,
                'logoUrls' => ['https://example.com/orange.png'],
            ],
            [
                'operatorId' => 254,
                'name' => 'MTN CI',
                'data' => false,
                'logoUrls' => ['https://example.com/mtn.png'],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/topup/operators');

    $response->assertOk()
        ->assertJson([
            [
                'name' => 'Orange CI',
                'id' => 253,
                'logo_url' => 'https://example.com/orange.png',
            ],
        ])
        ->assertJsonMissing([
            'name' => 'MTN CI',
        ])
        ->assertJsonMissingPath('0.data')
        ->assertJsonMissingPath('0.operatorId');

    Http::assertSent(function (Request $request) {
        return str_starts_with($request->url(), 'https://topups.reloadly.com/operators/countries/CI')
            && (bool) $request['includeData'] === true
            && $request->hasHeader('Authorization', 'Bearer reloadly-token');
    });
});

test('it lists data plans for operators by country code', function () {
    Cache::flush();

    $user = User::factory()->create([
        'country_code' => 'CI',
    ]);

    Sanctum::actingAs($user);

    Http::preventStrayRequests();
    Http::fake([
        'https://auth.reloadly.com/oauth/token' => Http::response([
            'access_token' => 'reloadly-token',
            'expires_in' => 3600,
        ]),
        'https://topups.reloadly.com/operators/countries/CI*' => Http::response([
            [
                'operatorId' => 253,
                'name' => 'Orange CI',
                'data' => true,
                'logoUrls' => ['https://example.com/orange.png'],
                'localMinAmount' => 100,
                'localMaxAmount' => 10000,
                'localFixedAmounts' => [500, 1000],
                'localFixedAmountsDescriptions' => [
                    '500' => '500MB - 1 Day',
                    '1000' => '1GB - 7 Days',
                ],
            ],
            [
                'operatorId' => 254,
                'name' => 'Voice Only',
                'data' => false,
                'localFixedAmounts' => [200],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/topup/data-plans');

    $response->assertOk()
        ->assertJson([
            'country_code' => 'CI',
            'operators' => [
                [
                    'id' => 253,
                    'name' => 'Orange CI',
                    'logo_url' => 'https://example.com/orange.png',
                    'min_amount' => 100,
                    'max_amount' => 10000,
                    'plans' => [
                        [
                            'amount' => 500,
                            'description' => '500MB - 1 Day',
                        ],
                        [
                            'amount' => 1000,
                            'description' => '1GB - 7 Days',
                        ],
                    ],
                ],
            ],
        ])
        ->assertJsonMissing([
            'name' => 'Voice Only',
        ]);

    Http::assertSent(function (Request $request) {
        return str_starts_with($request->url(), 'https://topups.reloadly.com/operators/countries/CI')
            && (bool) $request['includeData'] === true
            && $request->hasHeader('Authorization', 'Bearer reloadly-token');
    });
});

test('it lists data plans for a selected operator', function () {
    Cache::flush();

    $user = User::factory()->create([
        'country_code' => 'CI',
    ]);

    Sanctum::actingAs($user);

    Http::preventStrayRequests();
    Http::fake([
        'https://auth.reloadly.com/oauth/token' => Http::response([
            'access_token' => 'reloadly-token',
            'expires_in' => 3600,
        ]),
        'https://topups.reloadly.com/operators/253' => Http::response([
            'operatorId' => 253,
            'name' => 'Orange CI',
            'data' => true,
            'localFixedAmounts' => [500, 1000],
            'localFixedAmountsDescriptions' => [
                '500' => '500MB - 1 Day',
                '1000' => '1GB - 7 Days',
            ],
        ]),
    ]);

    $response = $this->getJson('/api/topup/operators/253/data-plans');

    $response->assertOk()
        ->assertJson([
            [
                'id' => '253-500',
                'name' => '500MB - 1 Day',
                'price' => 500.0,
                'dataAmount' => '500MB',
                'validity' => '1 Day',
                'description' => '500MB - 1 Day',
            ],
            [
                'id' => '253-1000',
                'name' => '1GB - 7 Days',
                'price' => 1000.0,
                'dataAmount' => '1GB',
                'validity' => '7 Days',
                'description' => '1GB - 7 Days',
            ],
        ]);

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://topups.reloadly.com/operators/253'
            && $request->hasHeader('Authorization', 'Bearer reloadly-token');
    });
});

test('it uses the authenticated users country code when none is provided', function () {
    Cache::flush();

    $user = User::factory()->create([
        'country_code' => 'CI',
    ]);

    Sanctum::actingAs($user);

    Http::preventStrayRequests();
    Http::fake([
        'https://auth.reloadly.com/oauth/token' => Http::response([
            'access_token' => 'reloadly-token',
            'expires_in' => 3600,
        ]),
        'https://topups.reloadly.com/operators/countries/CI*' => Http::response([]),
    ]);

    $response = $this->getJson('/api/topup/data-plans');

    $response->assertOk()
        ->assertJson([
            'country_code' => 'CI',
            'operators' => [],
        ]);
});
