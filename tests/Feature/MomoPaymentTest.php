<?php

use App\Models\MomoPayment;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a momo payment via factory', function () {
    $momoPayment = MomoPayment::factory()->create();

    expect($momoPayment)->toBeInstanceOf(MomoPayment::class)
        ->and($momoPayment->transaction)->toBeInstanceOf(Transaction::class);
});

it('has fillable attributes', function () {
    $data = [
        'transaction_id' => Transaction::factory()->create()->id,
        'reference' => 'TEST-123',
        'amount' => 100.50,
        'fee' => 1.50,
        'service' => 'Test Service',
        'service_code' => 'TEST',
        'recipient_number' => '123456789',
        'status' => 'pending',
        'message' => 'Test message',
    ];

    $momoPayment = MomoPayment::create($data);

    expect($momoPayment->reference)->toBe('TEST-123')
        ->and((float) $momoPayment->amount)->toBe(100.50)
        ->and((float) $momoPayment->fee)->toBe(1.50);
});

it('belongs to a transaction', function () {
    $transaction = Transaction::factory()->create();
    $momoPayment = MomoPayment::factory()->create(['transaction_id' => $transaction->id]);

    expect($momoPayment->transaction->id)->toBe($transaction->id);
});

it('can be accessed from a transaction', function () {
    $transaction = Transaction::factory()->create();
    $momoPayment = MomoPayment::factory()->create(['transaction_id' => $transaction->id]);

    expect($transaction->momoPayment->id)->toBe($momoPayment->id);
});
