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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->foreignId('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->foreignId('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('request_method')->nullable();
            $table->string('request_path')->nullable();
            $table->string('request_status_code')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['log_name']);
            $table->index(['causer_id', 'causer_type']);
            $table->index(['subject_id', 'subject_type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};