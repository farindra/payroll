<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_period_id');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('pph_21', 15, 2)->default(0);
            $table->decimal('bpjs_kesehatan_emp', 15, 2)->default(0);
            $table->decimal('bpjs_tk_emp', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('net_salary', 15, 2);
            $table->string('payslip_path')->nullable();
            $table->text('calculation_details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['payroll_period_id', 'employee_id']);
            $table->index(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};