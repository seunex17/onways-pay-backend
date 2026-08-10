<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: 2026_08_10_072158_add_columns_to_exchanges_table.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 8/10/26
 * Time: 8:21 AM
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->string('from_country_code', 3)->nullable()->after('to_currency');
            $table->string('to_country_code', 3)->nullable()->after('from_country_code');
        });
    }

    public function down(): void
    {
        Schema::table('exchanges', function (Blueprint $table) {
            //
        });
    }
};
