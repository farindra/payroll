<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('country')->default('Indonesia');
            $table->enum('tax_type', ['income_tax', 'social_security', 'medicare', 'other']);
            $table->decimal('min_income', 15, 2);
            $table->decimal('max_income', 15, 2)->nullable();
            $table->decimal('rate', 5, 2);
            $table->decimal('fixed_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};