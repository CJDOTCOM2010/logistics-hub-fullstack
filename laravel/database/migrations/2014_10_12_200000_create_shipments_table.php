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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pickup_address_id')->constrained('addresses')->onDelete('cascade');
            $table->foreignId('delivery_address_id')->constrained('addresses')->onDelete('cascade');
            $table->enum('shipment_type', ['standard', 'express', 'overnight', 'same_day', 'international', 'fragile', 'hazardous']);
            $table->enum('status', [
                'draft', 'pending', 'confirmed', 'assigned', 'pickup_scheduled',
                'picked_up', 'in_transit', 'out_for_delivery', 'delivered',
                'failed_delivery', 'cancelled', 'returned', 'on_hold'
            ])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('description')->nullable();
            $table->json('items')->nullable(); // JSON array of shipment items
            $table->decimal('weight', 10, 2)->nullable(); // Total weight in kg
            $table->decimal('dimensions_length', 8, 2)->nullable(); // cm
            $table->decimal('dimensions_width', 8, 2)->nullable(); // cm
            $table->decimal('dimensions_height', 8, 2)->nullable(); // cm
            $table->decimal('declared_value', 15, 2)->default(0.00);
            $table->decimal('shipping_cost', 15, 2)->default(0.00);
            $table->decimal('insurance_cost', 15, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->decimal('distance', 10, 2)->nullable(); // Distance in km
            $table->integer('estimated_duration')->nullable(); // Estimated duration in minutes
            $table->datetime('pickup_scheduled_at')->nullable();
            $table->datetime('pickup_completed_at')->nullable();
            $table->datetime('delivery_scheduled_at')->nullable();
            $table->datetime('delivery_completed_at')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->json('proof_of_delivery')->nullable(); // JSON with images, signatures, etc.
            $table->text('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->boolean('signature_required')->default(false);
            $table->boolean('insurance_requested')->default(false);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'wallet', 'invoice'])->default('cash');
            $table->string('external_reference')->nullable(); // For external system integration
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tracking_number']);
            $table->index(['customer_id', 'status']);
            $table->index('driver_id');
            $table->index('vehicle_id');
            $table->index(['status', 'priority']);
            $table->index(['pickup_scheduled_at', 'delivery_scheduled_at']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};