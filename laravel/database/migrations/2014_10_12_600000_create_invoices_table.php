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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['standard', 'proforma', 'credit', 'debit', 'recurring']);
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->storedAs('subtotal + tax_amount - discount_amount');
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->storedAs('total_amount - paid_amount');
            $table->string('currency', 3)->default('USD');
            $table->json('items'); // Array of invoice items
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('shipping_address')->nullable();
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('set null');
            $table->json('payment_terms')->nullable();
            $table->boolean('auto_reminder_enabled')->default(true);
            $table->datetime('sent_at')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->datetime('last_reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['invoice_number']);
            $table->index(['customer_id', 'status']);
            $table->index(['created_by']);
            $table->index(['status', 'due_date']);
            $table->index('shipment_id');
            $table->index(['invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};