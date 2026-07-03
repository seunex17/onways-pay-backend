<?php

use App\Mail\PasswordResetMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\TransactionPin;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

test('user can request phone verification OTP', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'phone_code' => '+234',
    ]);

    $response = $this->postJson('/api/send-phone-verification', [
        'email' => 'user@example.com',
        'phone' => '8123456789',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('phone_otp_sent'),
        ]);

    $this->assertEquals($user->id, $response->json('user.id'));
});

test('phone verification fails if email not found', function () {
    $response = $this->postJson('/api/send-phone-verification', [
        'email' => 'nonexistent@example.com',
        'phone' => '8123456789',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => __('email_not_found'),
        ]);
});

test('phone verification fails if phone number already exists for another user', function () {
    // Create another user with the same phone number
    User::factory()->create([
        'email' => 'other@example.com',
        'phone_code' => '+234',
        'phone' => '8123456789',
    ]);

    // Current user trying to use that phone number
    User::factory()->create([
        'email' => 'user@example.com',
        'phone_code' => '+234',
    ]);

    $response = $this->postJson('/api/send-phone-verification', [
        'email' => 'user@example.com',
        'phone' => '8123456789',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => __('phone_already_verified'),
        ]);
});

test('phone verification succeeds if phone number belongs to the same user', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'phone_code' => '+234',
        'phone' => '8123456789',
    ]);

    $response = $this->postJson('/api/send-phone-verification', [
        'email' => 'user@example.com',
        'phone' => '8123456789',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('phone_otp_sent'),
        ]);
});

test('user can verify phone with valid token', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'phone' => '8123456789',
        'phone_verified_at' => null,
    ]);

    $otp = (new Otp)->generate($user->phone, 'numeric', 6, 10);

    $response = $this->postJson('/api/verify-phone', [
        'email' => $user->email,
        'token' => $otp->token,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('phone_verified'),
        ]);

    $this->assertNotNull($user->fresh()->phone_verified_at);
});

test('verify phone fails with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'phone' => '8123456789',
    ]);

    $response = $this->postJson('/api/verify-phone', [
        'email' => $user->email,
        'token' => 'invalid-otp',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => __('invalid_phone_otp'),
        ]);
});

test('verify phone fails if email not found', function () {
    $response = $this->postJson('/api/verify-phone', [
        'email' => 'nonexistent@example.com',
        'token' => '123456',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => __('email_not_found'),
        ]);
});

test('user can set transaction pin', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $response = $this->postJson('/api/set-transaction-pin', [
        'email' => 'user@example.com',
        'pin' => '123456',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('transaction_pin_updated'),
        ]);

    $this->assertDatabaseHas('transaction_pins', [
        'user_id' => $user->id,
    ]);

    $transactionPin = TransactionPin::where('user_id', $user->id)->first();
    $this->assertTrue(Hash::check('123456', $transactionPin->pin));

    Mail::assertSent(WelcomeMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('setting transaction pin fails if email not found', function () {
    $response = $this->postJson('/api/set-transaction-pin', [
        'email' => 'nonexistent@example.com',
        'pin' => '123456',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => __('email_not_found'),
        ]);
});

test('user can request a temporary password', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $user->createToken('mobile');

    $response = $this->postJson('/api/forget-password', [
        'email' => 'user@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => __('password_reset_success'),
        ]);

    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use ($user) {
        return $mail->hasTo($user->email)
            && strlen($mail->newPassword) === 16
            && Hash::check($mail->newPassword, $user->fresh()->password);
    });

    expect($user->tokens()->exists())->toBeFalse();
});

test('forget password fails if email not found', function () {
    Mail::fake();

    $response = $this->postJson('/api/forget-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => __('email_not_found'),
        ]);

    Mail::assertNothingSent();
});
