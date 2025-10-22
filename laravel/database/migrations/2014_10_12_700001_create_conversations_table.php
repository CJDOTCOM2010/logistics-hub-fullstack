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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id')->unique();
            $table->enum('type', ['direct', 'group', 'support', 'shipment']);
            $table->string('title')->nullable();
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('participants'); // Array of user IDs
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['active', 'archived', 'closed'])->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->foreignId('last_message_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('settings')->nullable(); // JSON for conversation settings
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['conversation_id']);
            $table->index(['type', 'status']);
            $table->index('shipment_id');
            $table->index(['created_by']);
            $table->index(['last_message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};