<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('component_id');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_percentage')->default(false);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'component_id', 'effective_date'], 'emp_comp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_components');
    }
};