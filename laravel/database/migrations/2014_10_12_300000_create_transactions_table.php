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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('transaction_type', ['payment', 'refund', 'payout', 'fee', 'penalty', 'bonus', 'deposit', 'withdrawal']);
            $table->enum('payment_method', ['stripe', 'paypal', 'paystack', 'flutterwave', 'wallet', 'cash', 'bank_transfer', 'check']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('fee', 15, 2)->default(0.00);
            $table->decimal('tax', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->storedAs('amount - fee - tax');
            $table->text('description')->nullable();
            $table->json('payment_gateway_data')->nullable(); // Store gateway-specific data
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->string('gateway_status')->nullable();
            $table->datetime('processed_at')->nullable();
            $table->datetime('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->timestamps();

            // Indexes
            $table->index(['transaction_id']);
            $table->index(['user_id', 'status']);
            $table->index(['shipment_id']);
            $table->index(['transaction_type', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index(['processed_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};