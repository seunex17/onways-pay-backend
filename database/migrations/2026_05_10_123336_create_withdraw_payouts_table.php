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
        Schema::create('withdraw_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid');
            $table->string('payment_reference');
            $table->integer('tries');
            $table->timestamp('next_retry')->nullable();
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'retrying', 'failed', 'complete'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_payouts');
    }
};
