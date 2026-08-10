<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: 2026_08_09_230929_create_mono_recipients_table.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 8/10/26
 * Time: 12:09 AM
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mono_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('phone_number');
            $table->string('country_code', 2);
            $table->string('phone_code', 3);
            $table->string('provider');
            $table->timestamp('last_used_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mono_recipients');
    }
};
