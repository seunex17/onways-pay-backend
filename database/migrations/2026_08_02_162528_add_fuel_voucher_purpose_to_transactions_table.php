<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('purpose', ['deposit', 'withdraw', 'transfer', 'exchange', 'topup', 'data', 'gift_card', 'fuel_voucher'])->default('withdraw')->change();
            $table->enum('status', ['pending', 'confirming', 'processing', 'processed', 'completed', 'failed', 'canceled', 'cancelled', 'expired'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('purpose', ['deposit', 'withdraw', 'transfer', 'exchange', 'topup', 'data', 'gift_card'])->default('withdraw')->change();
            $table->enum('status', ['pending', 'confirming', 'processing', 'processed', 'canceled', 'expired'])->default('pending')->change();
        });
    }
};
