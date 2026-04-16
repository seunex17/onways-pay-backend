<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: 2026_04_16_013103_update_status_column_in_exchanges_table.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 4/16/26
 * Time: 2:31 AM
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchanges', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'cancelled', 'hold'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('exchanges', function (Blueprint $table) {
            //
        });
    }
};
