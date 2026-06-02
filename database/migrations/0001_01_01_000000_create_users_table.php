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
        // NOTE: Duplicate with database/migrations/2024_01_01_000001_create_users_table.php
        // This migration file appears to be a second (conflicting) set of auth/session tables.
        // It's disabled here to avoid 'Table users already exists' during migrate.
        //
        // Original content:
        // Schema::create('users', function (Blueprint $table) { ... });
        // Schema::create('password_reset_tokens', function (Blueprint $table) { ... });
        //
        // Schema::create('sessions', function (Blueprint $table) { ... });


        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
