<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->decimal('break_hours', 3, 2)->default(0);
            $table->decimal('total_hours', 3, 2)->default(0);
            $table->decimal('overtime_hours', 3, 2)->default(0);
            $table->enum('status', ['Present', 'Sick', 'Permission', 'Leave', 'Absent'])->default('Present');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'date']);
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};