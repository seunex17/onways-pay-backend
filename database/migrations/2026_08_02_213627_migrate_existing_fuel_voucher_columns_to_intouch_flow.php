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
        Schema::table('fuel_voucher_purchases', function (Blueprint $table) {
            $table->renameColumn('quoted_amount', 'amount');
            $table->renameColumn('partner_payment_reference', 'touchpay_reference');
            $table->dropColumn('payment_expires_at');
        });

        Schema::table('fuel_vouchers', function (Blueprint $table) {
            $table->renameColumn('partner_reference', 'payout_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_voucher_purchases', function (Blueprint $table) {
            $table->renameColumn('amount', 'quoted_amount');
            $table->renameColumn('touchpay_reference', 'partner_payment_reference');
            $table->timestamp('payment_expires_at')->nullable();
        });

        Schema::table('fuel_vouchers', function (Blueprint $table) {
            $table->renameColumn('payout_reference', 'partner_reference');
        });
    }
};
