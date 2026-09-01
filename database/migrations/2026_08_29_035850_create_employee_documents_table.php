<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('document_type');
            $table->string('title');
            $table->string('reference_number')->nullable();
            $table->string('issuing_agency')->nullable();
            $table->date('date_issued')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('status')->default('Valid');
            $table->string('file_path')->nullable();
            $table->string('original_file_name')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('document_type');
            $table->index('expiration_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};