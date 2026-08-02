<?php

use App\Models\GiftCardOrder;
use App\Models\TransactionPin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it lists gift card products from reloadly', function () {
    Cache::flush();

    $user = User::factory()->create();

    Sanctum::actingAs($user);

    Http::preventStrayRequests();
    Http::fake([
        'https://auth.reloadly.com/oauth/token' => Http::response([
            'access_token' => 'gift-card-token',
            'expires_in' => 3600,
        ]),
        'https://giftcards.reloadly.com/products*' => Http::response([
            'content' => [
                [
                    'productId' => 10,
                    'productName' => 'Amazon US',
                ],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/gift-cards/products?country_code=US&product_name=Amazon');

    $response->assertOk()
        ->assertJson([
            'content' => [
                [
                    'productId' => 10,
                    'productName' => 'Amazon US',
                ],
            ],
        ]);

    Http::assertSent(function (Request $request) {
        return str_starts_with($request->url(), 'https://giftcards.reloadly.com/products')
            && $request['countryCode'] === 'US'
            && $request['productName'] === 'Amazon'
            && $request->hasHeader('Authorization', 'Bearer gift-card-token');
    });
});

test('it purchases a gift card and records the order', function () {
    Cache::flush();

    $user = User::factory()->create();
    $user->creditAdd(100.00, 'Test balance');

    TransactionPin::create([
        'user_id' => $user->id,
        'pin' => '123456',
    ]);

    Sanctum::actingAs($user);

    Http::preventStrayRequests();
    Http::fake([
        'https://auth.reloadly.com/oauth/token' => Http::response([
            'access_token' => 'gift-card-token',
            'expires_in' => 3600,
        ]),
        'https://giftcards.reloadly.com/orders' => Http::response([
            'transactionId' => 'RLD-GC-123',
            'status' => 'SUCCESSFUL',
            'cards' => [
                [
                    'pinCode' => 'CODE-123',
                ],
            ],
        ]),
    ]);

    $response = $this->postJson('/api/gift-cards/purchase', [
        'product_id' => 10,
        'product_name' => 'Amazon US',
        'country_code' => 'US',
        'currency_code' => 'USD',
        'unit_price' => 12.50,
        'quantity' => 2,
        'recipient_email' => 'ada@example.com',
        'transaction_pin' => '123456',
    ]);

    $response->assertOk()
        ->assertJson([
            'gift_card_order' => [
                'product_id' => 10,
                'product_name' => 'Amazon US',
                'reloadly_transaction_id' => 'RLD-GC-123',
                'status' => 'processed',
            ],
            'transaction' => [
                'type' => 'debit',
                'purpose' => 'gift_card',
                'amount' => 25,
                'status' => 'processed',
            ],
        ]);

    expect($user->fresh()->creditBalance())->toBe(75.0);

    $giftCardOrder = GiftCardOrder::first();

    expect($giftCardOrder)
        ->not->toBeNull()
        ->and($giftCardOrder->recipient_email)->toBe('ada@example.com')
        ->and($giftCardOrder->request_payload['productId'])->toBe(10)
        ->and($giftCardOrder->response_payload['transactionId'])->toBe('RLD-GC-123');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://giftcards.reloadly.com/orders'
            && $request['productId'] === 10
            && $request['quantity'] === 2
            && $request['unitPrice'] === 12.5
            && filled($request['customIdentifier']);
    });
});
