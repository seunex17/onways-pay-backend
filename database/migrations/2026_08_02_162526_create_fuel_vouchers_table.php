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
        Schema::create('fuel_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_voucher_purchase_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->text('code');
            $table->string('code_hash', 64)->unique();
            $table->text('qr_data');
            $table->string('status', 20)->default('pending');
            $table->decimal('litres', 10, 2);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('recipient_name', 120);
            $table->text('recipient_phone');
            $table->timestamp('expires_at');
            $table->string('partner_reference')->nullable();
            $table->string('station_id')->nullable();
            $table->string('partner_redemption_id')->nullable()->unique();
            $table->string('redemption_actor')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_vouchers');
    }
};
