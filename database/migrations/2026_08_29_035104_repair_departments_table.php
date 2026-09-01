<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('departments', 'code')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('code', 30)->nullable();
            });
        }

        if (! Schema::hasColumn('departments', 'name')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('name')->nullable();
            });
        }

        /*
         * Copy an existing department_name value into name
         * if the old table uses department_name.
         */
        if (
            Schema::hasColumn('departments', 'department_name') &&
            Schema::hasColumn('departments', 'name')
        ) {
            DB::table('departments')
                ->whereNull('name')
                ->update([
                    'name' => DB::raw('department_name'),
                ]);
        }

        if (! Schema::hasColumn('departments', 'office_head')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('office_head')->nullable();
            });
        }

        if (! Schema::hasColumn('departments', 'contact_number')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('contact_number')->nullable();
            });
        }

        if (! Schema::hasColumn('departments', 'email')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('email')->nullable();
            });
        }

        if (! Schema::hasColumn('departments', 'location')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('location')->nullable();
            });
        }

        if (! Schema::hasColumn('departments', 'description')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }

        if (! Schema::hasColumn('departments', 'status')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('status')->default('Active');
            });
        }
    }

    public function down(): void
    {
        /*
         * This repair migration intentionally does not remove columns
         * to prevent accidental loss of existing department data.
         */
    }
};