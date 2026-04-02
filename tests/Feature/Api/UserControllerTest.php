<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it returns user data when authenticated', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/check-login');

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

test('it returns user wallet balance when authenticated', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $user->creditAdd(100.50);

    $response = $this->getJson('/api/wallet-balance');

    $response->assertOk()
        ->assertJsonFragment([100.5]);
});

test('it returns unauthorized when not authenticated for wallet balance', function () {
    $response = $this->getJson('/api/wallet-balance');

    $response->assertUnauthorized();
});

test('it returns unauthorized when not authenticated', function () {
    $response = $this->getJson('/api/check-login');

    $response->assertUnauthorized();
});
