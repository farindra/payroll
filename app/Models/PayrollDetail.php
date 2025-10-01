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

    /**
     * Get earnings breakdown from calculation details
     */
    public function getEarningsAttribute()
    {
        $details = $this->calculation_details ?? [];
        $earnings = $details['earnings'] ?? [];

        // Clean UTF-8 encoding in earnings data
        return $this->cleanArrayUtf8($earnings);
    }

    /**
     * Get deductions breakdown from calculation details
     */
    public function getDeductionsAttribute()
    {
        $details = $this->calculation_details ?? [];
        $deductions = $details['deductions'] ?? [];

        // Clean UTF-8 encoding in deductions data
        return $this->cleanArrayUtf8($deductions);
    }

    /**
     * Clean UTF-8 encoding in array data
     */
    private function cleanArrayUtf8($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        return array_map(function($item) {
            if (is_array($item)) {
                return $this->cleanArrayUtf8($item);
            } elseif (is_string($item)) {
                return mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
            return $item;
        }, $data);
    }

    /**
     * Get base salary from calculation details or basic_salary field
     */
    public function getBaseSalaryAttribute()
    {
        return $this->basic_salary ?? ($this->calculation_details['basic_salary'] ?? 0);
    }

    /**
     * Get allowances from calculation details or total_allowances field
     */
    public function getAllowancesAttribute()
    {
        return $this->total_allowances ?? ($this->calculation_details['total_allowances'] ?? 0);
    }

    /**
     * Get tax amount with fallback
     */
    public function getTaxAttribute()
    {
        return $this->pph_21 ?? ($this->calculation_details['tax'] ?? 0);
    }

    /**
     * Get BPJS health amount with fallback
     */
    public function getBpjsHealthAttribute()
    {
        return $this->bpjs_kesehatan_emp ?? ($this->calculation_details['bpjs_health'] ?? 0);
    }

    /**
     * Get BPJS employment amount with fallback
     */
    public function getBpjsEmploymentAttribute()
    {
        return $this->bpjs_tk_emp ?? ($this->calculation_details['bpjs_employment'] ?? 0);
    }

    /**
     * Get other deductions amount
     */
    public function getOtherDeductionsAttribute()
    {
        $totalDeductions = $this->total_deductions ?? 0;
        $knownDeductions = ($this->tax ?? 0) + ($this->bpjs_health ?? 0) + ($this->bpjs_employment ?? 0);
        return max(0, $totalDeductions - $knownDeductions);
    }

    /**
     * Get overtime pay with fallback
     */
    public function getOvertimePayAttribute()
    {
        $earnings = $this->earnings;
        foreach ($earnings as $earning) {
            if (strtolower($earning['name']) === 'overtime' || strtolower($earning['name']) === 'lembur') {
                return $earning['amount'];
            }
        }
        return 0;
    }

    /**
     * Get bonus amount with fallback
     */
    public function getBonusAttribute()
    {
        $earnings = $this->earnings;
        foreach ($earnings as $earning) {
            if (strtolower($earning['name']) === 'bonus' || strtolower($earning['name']) === 'tunjangan kinerja') {
                return $earning['amount'];
            }
        }
        return 0;
    }
}