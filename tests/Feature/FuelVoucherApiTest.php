<?php

use App\Models\FuelStation;
use App\Models\FuelVoucher;
use App\Models\FuelVoucherPurchase;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'first_name' => 'Awa',
        'last_name' => 'Kone',
        'country' => 'CI',
        'country_code' => 'CI',
        'phone' => '0701020304',
        'phone_code' => '225',
    ]);
    Sanctum::actingAs($this->user);
    config()->set('fuel-vouchers.price_per_litre', 1250);
    config()->set('fuel-vouchers.currency', 'XOF');
});

test('a purchase calculates its amount and starts an InTouch mobile money collection', function () {
    fakeTouchPayCollection();

    $response = $this->withHeader('Idempotency-Key', '0df8384a-630b-482b-91c8-e6f34008faee')
        ->postJson('/api/fuel-vouchers/purchase', purchasePayload());

    $response->assertAccepted()
        ->assertJsonPath('status', 'awaiting_authorization')
        ->assertJsonPath('message', 'Approve the Mobile Money request on your phone.')
        ->assertJsonPath('transaction.purpose', 'fuel_voucher')
        ->assertJsonPath('transaction.amount', 25000)
        ->assertJsonPath('payment.partner_reference', 'MOMO-123')
        ->assertJsonPath('voucher', null);

    $purchase = FuelVoucherPurchase::firstOrFail();
    expect($purchase->payment_phone)->toBe('0506070809')
        ->and($purchase->getRawOriginal('payment_phone'))->not->toContain('0506070809');

    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && $request['amount'] === '25000.00'
        && $request['idFromClient'] === $purchase->transaction->reference);
});

test('purchase validation rejects secrets and malformed local phone numbers', function () {
    $response = $this->withHeader('Idempotency-Key', 'validation-key')
        ->postJson('/api/fuel-vouchers/purchase', [
            ...purchasePayload(),
            'payment_phone' => '+2250506070809',
            'pin' => '1234',
        ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('payment_phone');
});

test('fuel voucher responses use the requested French locale', function () {
    $this->withHeader('Accept-Language', 'fr')
        ->postJson('/api/fuel-vouchers/purchase', purchasePayload())
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Un en-tête Idempotency-Key valide est requis.');

    $voucher = voucherFor($this->user);

    $this->withHeader('Accept-Language', 'fr')
        ->get("/api/fuel-vouchers/{$voucher->id}/download")
        ->assertOk()
        ->assertSee('Bon de carburant');
});

test('an idempotency key cannot create a second purchase', function () {
    fakeTouchPayCollection();

    $headers = ['Idempotency-Key' => 'same-idempotency-key'];
    $this->postJson('/api/fuel-vouchers/purchase', purchasePayload(), $headers)->assertAccepted();
    $this->postJson('/api/fuel-vouchers/purchase', purchasePayload(), $headers)->assertConflict();

    expect(FuelVoucherPurchase::count())->toBe(1);
});

test('only a purchaser can view and download a voucher', function () {
    $voucher = voucherFor($this->user);

    $this->getJson("/api/fuel-vouchers/{$voucher->id}")->assertOk()->assertJsonPath('data.status', 'active');
    $this->get("/api/fuel-vouchers/{$voucher->id}/download")->assertOk()->assertHeader('Content-Type', 'image/svg+xml');

    Sanctum::actingAs(User::factory()->create(['phone' => '9999999999']));
    $this->getJson("/api/fuel-vouchers/{$voucher->id}")->assertNotFound();
});

test('a fuel partner can redeem an active voucher only once', function () {
    config()->set('fuel-vouchers.redemption_secret', 'partner-secret');
    FuelStation::factory()->create(['station_id' => 'STATION-104']);
    Http::fake([
        'https://apidist.gutouch.net/apidist/sec/WINTA9061/cashin' => Http::response(['status' => 'PENDING']),
    ]);
    $voucher = voucherFor($this->user, '7392-1084-5521');
    $qrData = Crypt::encryptString(json_encode(['reference' => $voucher->reference, 'code' => '7392-1084-5521'], JSON_THROW_ON_ERROR));
    $voucher->update(['qr_data' => $qrData]);
    $payload = ['qr_data' => $qrData, 'station_id' => 'STATION-104', 'partner_redemption_id' => 'POS-882910'];

    $this->withToken('partner-secret')->postJson('/api/partner/fuel-vouchers/redeem', $payload)
        ->assertOk()->assertJsonPath('status', 'redeemed');
    $this->withToken('partner-secret')->postJson('/api/partner/fuel-vouchers/redeem', $payload)
        ->assertConflict();

    expect($voucher->fresh()->status)->toBe('redeemed');

    Http::assertSent(fn ($request) => $request->url() === 'https://apidist.gutouch.net/apidist/sec/WINTA9061/cashin'
        && (float) $request['amount'] === 25000.0
        && $request['recipient_phone_number'] === '0506070809');
});

test('a verified successful callback issues one voucher and completes the transaction', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'purpose' => 'fuel_voucher',
        'amount' => 25000,
        'status' => 'pending',
    ]);
    $purchase = FuelVoucherPurchase::factory()->create([
        'user_id' => $this->user->id,
        'transaction_id' => $transaction->id,
        'provider' => 'orange-money',
        'touchpay_reference' => 'MOMO-123',
    ]);
    Event::fake();

    $payload = [
        'partner_transaction_id' => $transaction->reference,
        'status' => 'SUCCESSFUL',
    ];

    $this->postJson('/webhook/touchpay', $payload)->assertOk();
    $this->postJson('/webhook/touchpay', $payload)->assertOk();

    expect($purchase->fresh()->status)->toBe('completed')
        ->and($transaction->fresh()->status)->toBe('completed')
        ->and(FuelVoucher::count())->toBe(1);
});

function purchasePayload(): array
{
    return [
        'litres' => 20,
        'recipient_type' => 'other',
        'recipient_name' => 'Awa Kone',
        'recipient_phone' => '0701020304',
        'recipient_phone_code' => '225',
        'payment_method' => 'mobile_money',
        'payment_provider' => 'orange-money',
        'payment_phone' => '0506070809',
        'payment_phone_code' => '225',
    ];
}

function fakeTouchPayCollection(): void
{
    Http::fake([
        'https://apidist.gutouch.net/apidist/sec/touchpayapi/*' => Http::response([
            'idFromClient' => 'MOMO-123',
            'amount' => 25000,
            'fees' => 250,
            'serviceCode' => 'PAIEMENTMARCHANDOMPAYCIDIRECT',
            'recipientNumber' => '0506070809',
            'status' => 'PENDING',
        ]),
    ]);
}

function voucherFor(User $user, string $code = '1111-2222-3333'): FuelVoucher
{
    $transaction = Transaction::factory()->create(['user_id' => $user->id, 'purpose' => 'fuel_voucher']);
    $purchase = FuelVoucherPurchase::factory()->create(['user_id' => $user->id, 'transaction_id' => $transaction->id]);

    return FuelVoucher::factory()->create([
        'user_id' => $user->id,
        'fuel_voucher_purchase_id' => $purchase->id,
        'code' => $code,
        'code_hash' => hash('sha256', $code),
        'recipient_phone' => $user->phone,
    ]);
}
