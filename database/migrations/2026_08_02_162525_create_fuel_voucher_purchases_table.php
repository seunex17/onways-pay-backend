<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fuel_voucher_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->decimal('litres', 10, 2);
            $table->decimal('quoted_amount', 15, 2);
            $table->string('currency', 3);
            $table->string('recipient_type', 10);
            $table->string('recipient_name', 120);
            $table->text('recipient_phone');
            $table->string('recipient_phone_code', 4);
            $table->text('payment_phone');
            $table->string('payment_phone_code', 4);
            $table->string('status', 30)->default('pending');
            $table->string('idempotency_key');
            $table->string('partner_payment_reference')->nullable();
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'idempotency_key']);
            $table->index(['provider', 'partner_payment_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_voucher_purchases');
    }
};
