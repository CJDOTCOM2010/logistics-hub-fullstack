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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern', 'temporary', 'consultant']);
            $table->enum('employment_status', ['active', 'inactive', 'on_leave', 'terminated', 'resigned', 'retired'])->default('active');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('pay_frequency', 20)->default('monthly'); // weekly, bi-weekly, monthly
            $table->string('work_schedule')->nullable(); // JSON for schedule details
            $table->string('work_location')->nullable();
            $table->boolean('remote_work_allowed')->default(false);
            $table->text('job_description')->nullable();
            $table->text('skills')->nullable(); // JSON array of skills
            $table->json('emergency_contacts')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_routing_number')->nullable();
            $table->string('tax_id_number')->nullable();
            $table->json('benefits')->nullable(); // Health insurance, retirement, etc.
            $table->json('performance_reviews')->nullable(); // Array of performance review data
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id']);
            $table->index(['department_id']);
            $table->index(['position_id']);
            $table->index(['manager_id']);
            $table->index(['employment_type', 'employment_status']);
            $table->index('hire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};