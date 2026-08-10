<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: 2026_08_10_075053_add_column_to_momo_payments_table.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 8/10/26
 * Time: 8:50 AM
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('momo_payments', function (Blueprint $table) {
            $table->string('recipient_country_code', 3)->nullable()->after('service_code');
        });
    }

    public function down(): void
    {
        Schema::table('momo_payments', function (Blueprint $table) {
            //
        });
    }
};
