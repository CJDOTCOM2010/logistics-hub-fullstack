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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->decimal('budget', 15, 2)->nullable();
            $table->integer('employee_count')->default(0);
            $table->json('contact_info')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status']);
            $table->index(['parent_department_id']);
            $table->index('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};