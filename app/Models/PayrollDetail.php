<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'pph_21',
        'bpjs_kesehatan_emp',
        'bpjs_tk_emp',
        'gross_salary',
        'net_salary',
        'payslip_path',
        'calculation_details',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'pph_21' => 'decimal:2',
        'bpjs_kesehatan_emp' => 'decimal:2',
        'bpjs_tk_emp' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'calculation_details' => 'array',
    ];

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeByPeriod($query, $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}