<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });

        Schema::table('employee_components', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('component_id')->references('id')->on('salary_components')->onDelete('cascade');
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::table('payroll_details', function (Blueprint $table) {
            $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['manager_id']);
        });

        Schema::table('employee_components', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['component_id']);
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('payroll_details', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_id']);
            $table->dropForeign(['employee_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
    }
};