<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['allowance', 'deduction', 'tax', 'insurance']);
            $table->boolean('is_fixed')->default(false);
            $table->string('formula_code')->nullable();
            $table->decimal('default_amount', 15, 2)->nullable();
            $table->boolean('is_percentage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};