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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->enum('vehicle_type', ['truck', 'van', 'motorcycle', 'container_truck', 'tanker', 'flatbed', 'refrigerated', 'dump_truck', 'pickup']);
            $table->enum('fuel_type', ['diesel', 'petrol', 'electric', 'hybrid', 'lpg', 'cng']);
            $table->string('license_plate')->unique();
            $table->string('vin_number')->unique()->nullable();
            $table->decimal('capacity_weight', 10, 2)->nullable(); // in tons
            $table->decimal('capacity_volume', 10, 2)->nullable(); // in cubic meters
            $table->integer('axles')->default(2);
            $table->enum('status', ['available', 'in_use', 'maintenance', 'out_of_service', 'retired'])->default('available');
            $table->enum('ownership', ['owned', 'leased', 'rented', 'partner'])->default('owned');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null'); // Vehicle owner
            $table->text('features')->nullable(); // JSON for vehicle features
            $table->date('registration_expiry')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('mileage')->default(0);
            $table->decimal('fuel_efficiency', 5, 2)->nullable(); // km/l or mpg
            $table->text('documents')->nullable(); // JSON for document URLs
            $table->boolean('gps_enabled')->default(true);
            $table->string('gps_device_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['vehicle_type', 'status']);
            $table->index('driver_id');
            $table->index('owner_id');
            $table->index('license_plate');
            $table->index('registration_expiry');
            $table->index('insurance_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};