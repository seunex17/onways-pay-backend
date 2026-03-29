<?php

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('user can register with valid data', function () {
    Mail::fake();

    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('registration_success'),
        ]);

    $this->assertDatabaseHas('users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'name' => 'John Doe',
        'email' => 'test@example.com',
    ]);

    Mail::assertSent(VerifyEmailMail::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });
});

test('registration fails with invalid email', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(400);
});

test('registration fails with short password', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'short',
    ]);

    $response->assertStatus(400);
});

test('registration fails with missing fields', function () {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(400);
});

test('registration fails if email is already taken', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(400);
});
