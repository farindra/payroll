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
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('total_hours', 5, 2)->default(0)->change();
            $table->decimal('overtime_hours', 5, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('total_hours', 3, 2)->default(0)->change();
            $table->decimal('overtime_hours', 3, 2)->default(0)->change();
        });
    }
};
