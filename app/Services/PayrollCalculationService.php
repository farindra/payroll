<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Attendance;
use App\Models\EmployeeComponent;
use App\Models\Deduction;
use App\Models\PayrollDetail;
use App\Models\TaxRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollCalculationService
{
    protected $employee;
    protected $payrollPeriod;
    protected $attendances;
    protected $workingDays = 0;
    protected $presentDays = 0;
    protected $calculationDetails = [];

    public function processPayroll(PayrollPeriod $payrollPeriod)
    {
        DB::beginTransaction();
        try {
            $employees = Employee::where('employment_status', 'active')->get();
            $totalProcessed = 0;

            foreach ($employees as $employee) {
                $this->employee = $employee;
                $this->payrollPeriod = $payrollPeriod;
                $this->attendances = $this->getAttendances();

                $payrollDetail = $this->calculatePayroll();

                if ($payrollDetail) {
                    $totalProcessed++;
                }
            }

            $payrollPeriod->status = 'Calculated';
            $payrollPeriod->total_employees = $totalProcessed;
            $payrollPeriod->updateTotals();
            $payrollPeriod->save();

            DB::commit();
            return [
                'success' => true,
                'message' => "Payroll processed successfully for {$totalProcessed} employees",
                'processed_employees' => $totalProcessed
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payroll processing failed: ' . $e->getMessage()
            ];
        }
    }

    protected function getAttendances()
    {
        return Attendance::where('employee_id', $this->employee->id)
            ->whereBetween('date', [
                $this->payrollPeriod->start_date,
                $this->payrollPeriod->end_date
            ])
            ->get();
    }

    protected function calculatePayroll()
    {
        $this->calculateAttendanceStats();
        $basicSalary = $this->calculateBasicSalary();
        $allowances = $this->calculateAllowances();
        $overtime = $this->calculateOvertime();

        $grossSalary = $basicSalary + $allowances + $overtime;

        $pph21 = $this->calculatePPh21($grossSalary);
        $bpjsKesehatan = $this->calculateBPJSKesehatan();
        $bpjsTK = $this->calculateBPJSTK();
        $bpjsKesehatanCompany = $this->calculateBPJSKesehatanCompany();
        $bpjsTKCompany = $this->calculateBPJSTKCompany();
        $otherDeductions = $this->calculateOtherDeductions();

        $totalDeductions = $pph21 + $bpjsKesehatan + $bpjsTK + $otherDeductions;
        $netSalary = $grossSalary - $totalDeductions;

        $this->calculationDetails = [
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'overtime' => $overtime,
            'gross_salary' => $grossSalary,
            'pph21' => $pph21,
            'bpjs_kesehatan' => $bpjsKesehatan,
            'bpjs_tk' => $bpjsTK,
            'bpjs_kesehatan_company' => $bpjsKesehatanCompany,
            'bpjs_tk_company' => $bpjsTKCompany,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'working_days' => $this->workingDays,
            'present_days' => $this->presentDays,
            'attendance_summary' => $this->getAttendanceSummary()
        ];

        return PayrollDetail::updateOrCreate(
            [
                'payroll_period_id' => $this->payrollPeriod->id,
                'employee_id' => $this->employee->id,
            ],
            [
                'basic_salary' => $basicSalary,
                'total_allowances' => $allowances + $overtime,
                'total_deductions' => $totalDeductions,
                'pph_21' => $pph21,
                'bpjs_kesehatan_emp' => $bpjsKesehatan,
                'bpjs_tk_emp' => $bpjsTK,
                'bpjs_kesehatan_comp' => $bpjsKesehatanCompany,
                'bpjs_tk_comp' => $bpjsTKCompany,
                'gross_salary' => $grossSalary,
                'net_salary' => $netSalary,
                'calculation_details' => $this->calculationDetails,
            ]
        );
    }

    protected function calculateAttendanceStats()
    {
        $this->workingDays = $this->attendances->count();
        $this->presentDays = $this->attendances->where('status', 'Present')->count();
    }

    protected function calculateBasicSalary()
    {
        if ($this->workingDays == 0) return 0;

        $dailyRate = $this->employee->basic_salary / 22; // Standard working days per month
        return $dailyRate * $this->presentDays;
    }

    protected function calculateAllowances()
    {
        $totalAllowances = 0;
        $components = EmployeeComponent::where('employee_id', $this->employee->id)
            ->whereHas('component', function($query) {
                $query->where('type', 'allowance')->where('is_active', true);
            })
            ->where('effective_date', '<=', $this->payrollPeriod->end_date)
            ->where(function($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $this->payrollPeriod->start_date);
            })
            ->get();

        foreach ($components as $component) {
            if ($component->is_percentage) {
                $totalAllowances += ($this->employee->basic_salary * $component->amount) / 100;
            } else {
                $totalAllowances += $component->amount;
            }
        }

        return $totalAllowances;
    }

    protected function calculateOvertime()
    {
        $totalOvertime = 0;
        $overtimeRate = 1.5; // Standard overtime rate
        $hourlyRate = $this->employee->basic_salary / (22 * 8); // Daily hours

        foreach ($this->attendances as $attendance) {
            if ($attendance->overtime_hours > 0) {
                $totalOvertime += $attendance->overtime_hours * $hourlyRate * $overtimeRate;
            }
        }

        return $totalOvertime;
    }

    protected function calculatePPh21($grossSalary)
    {
        // Job Position Deduction (5% max 500,000)
        $jobDeduction = min($grossSalary * 0.05, 500000);

        // BPJS deductions
        $bpjsDeductions = $this->calculateBPJSKesehatan() + $this->calculateBPJSTK();

        // Taxable income
        $taxableIncome = $grossSalary - $jobDeduction - $bpjsDeductions;

        // Get PTKP allowance based on marital status
        $ptkpAmount = $this->getPTKPAmount();

        // Annual taxable income
        $annualTaxableIncome = ($taxableIncome * 12) - $ptkpAmount;

        if ($annualTaxableIncome <= 0) return 0;

        // Calculate annual tax
        $annualTax = $this->calculateProgressiveTax($annualTaxableIncome);

        // Monthly tax
        return $annualTax / 12;
    }

    protected function getPTKPAmount()
    {
        $ptkpRates = [
            'TK/0' => 54000000,
            'TK/1' => 58500000,
            'TK/2' => 63000000,
            'TK/3' => 67500000,
            'K/0' => 54000000 + 4500000,
            'K/1' => 58500000 + 4500000,
            'K/2' => 63000000 + 4500000,
            'K/3' => 67500000 + 4500000,
        ];

        return $ptkpRates[$this->employee->ptkp_status] ?? 54000000;
    }

    protected function calculateProgressiveTax($annualIncome)
    {
        $taxBrackets = [
            ['min' => 0, 'max' => 60000000, 'rate' => 5],
            ['min' => 60000000, 'max' => 250000000, 'rate' => 15],
            ['min' => 250000000, 'max' => 500000000, 'rate' => 25],
            ['min' => 500000000, 'max' => 5000000000, 'rate' => 30],
            ['min' => 5000000000, 'max' => null, 'rate' => 35],
        ];

        $totalTax = 0;
        $remainingIncome = $annualIncome;

        foreach ($taxBrackets as $bracket) {
            if ($remainingIncome <= 0) break;

            $bracketMax = $bracket['max'] ?? $remainingIncome;
            $bracketIncome = min($remainingIncome, $bracketMax - $bracket['min']);

            if ($bracketIncome > 0) {
                $totalTax += ($bracketIncome * $bracket['rate']) / 100;
                $remainingIncome -= $bracketIncome;
            }
        }

        return $totalTax;
    }

    protected function calculateBPJSKesehatan()
    {
        $salary = min($this->employee->basic_salary, 12000000); // Max 12 juta
        return $salary * 0.01; // 1%
    }

    protected function calculateBPJSTK()
    {
        $salary = min($this->employee->basic_salary, 8759400); // Max sesuai aturan
        return $salary * 0.02; // 2%
    }

    protected function calculateBPJSKesehatanCompany()
    {
        $salary = min($this->employee->basic_salary, 12000000); // Max 12 juta
        return $salary * 0.04; // 4%
    }

    protected function calculateBPJSTKCompany()
    {
        $salary = min($this->employee->basic_salary, 8759400); // Max sesuai aturan
        return $salary * 0.037; // 3.7%
    }

    protected function calculateOtherDeductions()
    {
        $totalDeductions = 0;
        $deductions = Deduction::where('employee_id', $this->employee->id)
            ->active()
            ->forDate($this->payrollPeriod->end_date)
            ->get();

        foreach ($deductions as $deduction) {
            $totalDeductions += $deduction->monthly_amount;
        }

        return $totalDeductions;
    }

    protected function getAttendanceSummary()
    {
        return $this->attendances->groupBy('status')->map(function ($group) {
            return $group->count();
        })->toArray();
    }
}