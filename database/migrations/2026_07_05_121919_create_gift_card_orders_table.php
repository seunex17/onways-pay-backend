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
        Schema::create('gift_card_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('reloadly_transaction_id')->nullable()->index();
            $table->unsignedBigInteger('product_id');
            $table->string('product_name')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_amount', 15, 2);
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_card_orders');
    }
};
