<?php

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
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

test('can resend email verification', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $response = $this->postJson('/api/resend-email-verification', [
        'email' => 'user@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('new_email_otp_sent'),
        ]);

    Mail::assertSent(VerifyEmailMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('resending email verification fails if email not found', function () {
    $response = $this->postJson('/api/resend-email-verification', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => __('email_not_found'),
        ]);
});

test('user can verify email with valid token', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'email_verified_at' => null,
    ]);

    $otp = (new Otp)->generate($user->email, 'numeric', 6, 10);

    $response = $this->postJson('/api/verify-email', [
        'email' => $user->email,
        'token' => $otp->token,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('email_verified'),
        ]);

    $this->assertNotNull($user->fresh()->email_verified_at);
});

test('email verification fails with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $response = $this->postJson('/api/verify-email', [
        'email' => $user->email,
        'token' => 'invalid-otp',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => __('invalid_email_otp'),
        ]);
});

test('email verification fails if email not found', function () {
    $response = $this->postJson('/api/verify-email', [
        'email' => 'nonexistent@example.com',
        'token' => '123456',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => __('email_not_found'),
        ]);
});
