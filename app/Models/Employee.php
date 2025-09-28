<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nip',
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'hire_date',
        'employment_status',
        'position',
        'department_id',
        'manager_id',
        'bank_account_no',
        'bank_name',
        'bank_branch',
        'basic_salary',
        'npwp',
        'ptkp_status',
        'bpjs_kesehatan_no',
        'bpjs_tk_no',
        'profile_image',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function salaryComponents()
    {
        return $this->hasMany(EmployeeComponent::class);
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    public function getAnnualSalaryAttribute()
    {
        return $this->basic_salary * 12;
    }

    public function getMonthlyBPJSKesehatanAttribute()
    {
        $salary = min($this->basic_salary, 12000000); // Max 12 juta
        return $salary * 0.01; // 1%
    }

    public function getMonthlyBPJSTKAttribute()
    {
        $salary = min($this->basic_salary, 8759400); // Max sesuai aturan
        return $salary * 0.02; // 2%
    }
}