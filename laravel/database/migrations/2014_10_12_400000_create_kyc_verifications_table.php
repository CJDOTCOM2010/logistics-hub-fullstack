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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('verification_type', ['identity', 'address', 'business', 'driver', 'vehicle'])->default('identity');
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected', 'expired', 'requires_resubmission'])->default('pending');
            $table->string('document_type'); // passport, driver_license, national_id, utility_bill, etc.
            $table->string('document_number')->nullable();
            $table->date('document_expiry_date')->nullable();
            $table->date('document_issue_date')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->json('document_images')->nullable(); // URLs to uploaded document images
            $table->string('selfie_image')->nullable(); // URL to selfie with document
            $table->decimal('facial_match_score', 5, 2)->nullable(); // Facial recognition confidence score
            $table->text('verification_notes')->nullable(); // Admin notes
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who verified
            $table->datetime('verified_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->json('third_party_verification_data')->nullable(); // Data from external verification services
            $table->string('verification_reference')->nullable(); // External service reference
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'verification_type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('document_number');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};