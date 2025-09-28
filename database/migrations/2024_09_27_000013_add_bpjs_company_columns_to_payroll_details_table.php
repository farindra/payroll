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
            $table->decimal('bpjs_kesehatan_comp', 12, 2)->after('bpjs_kesehatan_emp')->default(0);
            $table->decimal('bpjs_tk_comp', 12, 2)->after('bpjs_tk_emp')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_details', function (Blueprint $table) {
            $table->dropColumn(['bpjs_kesehatan_comp', 'bpjs_tk_comp']);
        });
    }
};