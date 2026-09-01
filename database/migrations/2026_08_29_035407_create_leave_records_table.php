<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('leave_type');
            $table->date('date_filed');
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('number_of_days', 8, 2);
            $table->text('reason')->nullable();
            $table->boolean('with_pay')->default(true);
            $table->string('status')->default('Pending');
            $table->text('remarks')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('date_approved')->nullable();
            $table->timestamps();

            $table->index('leave_type');
            $table->index('status');
            $table->index('date_filed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_records');
    }
};