<?php
// database/migrations/2024_01_01_000002_create_jobs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // provider
            $table->string('title');
            $table->string('company');
            $table->string('location');
            $table->enum('type', ['full-time', 'part-time', 'remote', 'contract', 'internship'])->default('full-time');
            $table->text('description');
            $table->text('requirements'); // skills needed
            $table->string('experience_required')->nullable();
            $table->string('salary_range')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            // For FAISS-like matching: store comma-separated key skills
            $table->text('key_skills')->nullable();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // seeker
            $table->enum('status', ['applied', 'reviewed', 'shortlisted', 'rejected'])->default('applied');
            $table->integer('match_score')->default(0); // 0-100
            $table->timestamps();
            $table->unique(['job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('jobs');
    }
};
