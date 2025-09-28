<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Indonesia');
            $table->date('hire_date');
            $table->enum('employment_status', ['active', 'terminated', 'suspended', 'on_leave'])->default('active');
            $table->string('position');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->decimal('basic_salary', 15, 2);
            $table->string('npwp')->nullable();
            $table->enum('ptkp_status', ['K/0', 'TK/0', 'K/1', 'TK/1', 'K/2', 'TK/2', 'K/3', 'TK/3'])->default('TK/0');
            $table->string('bpjs_kesehatan_no')->nullable();
            $table->string('bpjs_tk_no')->nullable();
            $table->string('profile_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};