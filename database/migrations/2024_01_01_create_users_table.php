<?php
// database/migrations/2024_01_01_000001_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['seeker', 'provider']);
            // Seeker fields
            $table->text('skills')->nullable();
            $table->string('education')->nullable();
            $table->integer('experience_years')->nullable();
            $table->string('preferred_role')->nullable();
            $table->string('location')->nullable();
            $table->string('cv_path')->nullable();
            $table->text('profile_summary')->nullable(); // generated NL text
            // Provider fields
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();
            $table->text('company_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
