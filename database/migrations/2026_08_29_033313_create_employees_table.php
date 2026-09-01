<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();

            $table->string('sex');
            $table->date('birth_date')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            $table->string('department');
            $table->string('position');
            $table->unsignedTinyInteger('salary_grade')->nullable();
            $table->unsignedTinyInteger('step')->nullable();
            $table->string('employment_status')->default('Regular');
            $table->date('date_hired')->nullable();

            $table->string('gsis_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('tin_number')->nullable();

            $table->string('status')->default('Active');
            $table->timestamps();

            $table->index('last_name');
            $table->index('department');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};