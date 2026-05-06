<?php
	/**
	 * Copyright (C) ZubDev Digital Media - All Rights Reserved
	 *
	 * File: 2026_05_06_014754_add_column_to_users_table.php
	 * Author: Zubayr Ganiyu
	 *   Email: <seunexseun@gmail.com>
	 *   Website: https://zubdev.net
	 * Date: 5/6/26
	 * Time: 2:47 AM
	 */


	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration
	{
		public function up()
		: void
		{
			Schema::table('users', function(Blueprint $table)
			{
				$table->timestamp('account_delete_at')->nullable()->after('password');
			});
		}


		public function down()
		: void
		{
			Schema::table('users', function(Blueprint $table)
			{
				//
			});
		}
	};
