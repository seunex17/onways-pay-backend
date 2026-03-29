<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: 2026_03_28_211912_add_columns_to_users_table.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 3/28/26
 * Time: 10:19 PM
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->after('id');
            $table->string('customer_id')->after('uuid')->nullable();
            $table->string('first_name')->after('customer_id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('country')->after('last_name')->nullable();
            $table->string('country_code', 3)->after('country')->nullable();
            $table->string('country_flag', 3)->after('country_code')->nullable();
            $table->string('phone')->after('country_flag')->nullable();
            $table->enum('status', ['active', 'inactive'])->after('name')->default('active');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
