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
        Schema::create('shipment_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Who created this tracking point
            $table->enum('status', [
                'created', 'confirmed', 'assigned', 'pickup_scheduled', 'picked_up',
                'in_transit', 'out_for_delivery', 'delivered', 'failed_delivery',
                'cancelled', 'returned', 'on_hold', 'exception', 'custom'
            ]);
            $table->string('status_description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->boolean('visible_to_customer')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['shipment_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_tracking');
    }
};