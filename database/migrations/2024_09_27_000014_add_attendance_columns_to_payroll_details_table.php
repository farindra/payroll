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
        Schema::table('payroll_details', function (Blueprint $table) {
            $table->integer('working_days')->after('net_salary')->default(0);
            $table->integer('present_days')->after('working_days')->default(0);
            $table->integer('absent_days')->after('present_days')->default(0);
            $table->integer('sick_days')->after('absent_days')->default(0);
            $table->integer('leave_days')->after('sick_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->after('leave_days')->default(0);
            $table->decimal('overtime_pay', 15, 2)->after('overtime_hours')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_details', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'present_days', 'absent_days', 'sick_days', 'leave_days', 'overtime_hours', 'overtime_pay']);
        });
    }
};