<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->integer('installment_count')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_amount', 15, 2)->storedAs('amount / installment_count');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};