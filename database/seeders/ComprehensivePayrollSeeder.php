<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PayrollPeriod;
use App\Models\Attendance;
use App\Models\PayrollDetail;
use App\Models\EmployeeComponent;
use App\Models\Deduction;

class ComprehensivePayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting comprehensive payroll seeder for last 3 months...');

        // Define the months we'll seed (July, August, September 2025)
        $months = [
            ['month' => 7, 'year' => 2025, 'name' => 'Juli 2025'],
            ['month' => 8, 'year' => 2025, 'name' => 'Agustus 2025'],
            ['month' => 9, 'year' => 2025, 'name' => 'September 2025'],
        ];

        foreach ($months as $monthData) {
            $this->seedMonthData($monthData);
        }

        $this->command->info('Comprehensive payroll data seeded successfully!');
    }

    private function seedMonthData($monthData)
    {
        $this->command->info("Seeding data for {$monthData['name']}...");

        // Create payroll period
        $startDate = Carbon::create($monthData['year'], $monthData['month'], 1);
        $endDate = $startDate->copy()->endOfMonth();
        $paymentDate = $endDate->copy()->addDays(5);

        $period = PayrollPeriod::firstOrCreate(
            ['period_name' => $monthData['name']],
            [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'payment_date' => $paymentDate->format('Y-m-d'),
                'status' => 'Calculated',
                'notes' => "Monthly payroll for {$monthData['name']}",
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Get all active employees
        $employees = Employee::where('employment_status', 'active')->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No active employees found. Skipping payroll data.');
            return;
        }

        // Generate attendance data for the month
        $this->generateAttendanceData($period, $employees);

        // Generate payroll components (allowances/deductions)
        $this->generateEmployeeComponents($period, $employees);

        // Generate payroll details
        $this->generatePayrollDetails($period, $employees);

        $this->command->info("Completed seeding for {$monthData['name']}");
    }

    private function generateAttendanceData($period, $employees)
    {
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);

        // Working days (excluding weekends)
        $workingDays = 0;
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }

        foreach ($employees as $employee) {
            // Generate daily attendance
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                if ($date->isWeekend()) {
                    continue; // Skip weekends
                }

                // Random attendance scenarios
                $rand = mt_rand(1, 100);

                if ($rand <= 75) {
                    // Present (75% chance)
                    $status = 'Present';
                    $clockIn = Carbon::parse($date->format('Y-m-d') . ' ' . sprintf('%02d:%02d:%02d', mt_rand(7, 9), mt_rand(0, 59), 0));
                    $clockOut = Carbon::parse($date->format('Y-m-d') . ' ' . sprintf('%02d:%02d:%02d', mt_rand(16, 18), mt_rand(0, 59), 0));
                    $overtimeHours = mt_rand(0, 2) + (mt_rand(0, 1) * 0.5); // 0-2.5 hours
                } elseif ($rand <= 85) {
                    // Sick (10% chance)
                    $status = 'Sick';
                    $clockIn = null;
                    $clockOut = null;
                    $overtimeHours = 0;
                } elseif ($rand <= 95) {
                    // Leave (10% chance)
                    $status = 'Leave';
                    $clockIn = null;
                    $clockOut = null;
                    $overtimeHours = 0;
                } else {
                    // Absent (5% chance)
                    $status = 'Absent';
                    $clockIn = null;
                    $clockOut = null;
                    $overtimeHours = 0;
                }

                $totalHours = 0;
                $breakHours = 1; // Standard 1 hour break

                if ($clockIn && $clockOut) {
                    $totalHours = $clockOut->diffInHours($clockIn) - $breakHours;
                    // Cap at reasonable working hours (max 12 hours)
                    $totalHours = min($totalHours, 12);
                    $totalHours = max($totalHours, 0); // Ensure not negative
                }

                Attendance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d')
                    ],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'break_hours' => $breakHours,
                        'total_hours' => $totalHours,
                        'overtime_hours' => $overtimeHours,
                        'status' => $status,
                        'note' => $status === 'Present' ? null : "Auto-generated {$status} record",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function generateEmployeeComponents($period, $employees)
    {
        $salaryComponents = [
            ['name' => 'Tunjangan Transport', 'type' => 'allowance', 'amount' => 500000, 'is_percentage' => false],
            ['name' => 'Tunjangan Makan', 'type' => 'allowance', 'amount' => 300000, 'is_percentage' => false],
            ['name' => 'Tunjangan Kinerja', 'type' => 'allowance', 'amount' => 10, 'is_percentage' => true],
            ['name' => 'Tunjangan Komunikasi', 'type' => 'allowance', 'amount' => 200000, 'is_percentage' => false],
        ];

        foreach ($employees as $employee) {
            foreach ($salaryComponents as $component) {
                // Some employees get certain allowances (random selection)
                if (mt_rand(1, 100) <= 70) { // 70% chance of getting each allowance
                    EmployeeComponent::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'component_id' => $this->getOrCreateSalaryComponent($component),
                            'effective_date' => $period->start_date,
                        ],
                        [
                            'amount' => $component['amount'],
                            'is_percentage' => $component['is_percentage'],
                            'end_date' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            // Create some deductions for some employees
            if (mt_rand(1, 100) <= 30) { // 30% chance of having deductions
                $deductionTypes = [
                    ['type' => 'Pinjaman Koperasi', 'description' => 'Pinjaman dari koperasi perusahaan', 'amount' => 500000],
                    ['type' => 'Angsuran Peralatan', 'description' => 'Angsuran pembelian peralatan kantor', 'amount' => 250000],
                    ['type' => 'Denda Keterlambatan', 'description' => 'Denda keterlambatan kehadiran', 'amount' => 100000],
                ];

                $deduction = $deductionTypes[array_rand($deductionTypes)];

                Deduction::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'type' => $deduction['type'],
                        'start_date' => $period->start_date,
                    ],
                    [
                        'amount' => $deduction['amount'],
                        'installment_count' => 6,
                        'end_date' => Carbon::parse($period->start_date)->addMonths(6)->format('Y-m-d'),
                        'description' => $deduction['description'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function generatePayrollDetails($period, $employees)
    {
        $totalGrossSalary = 0;
        $totalNetSalary = 0;
        $totalEmployees = 0;

        foreach ($employees as $employee) {
            // Get attendance data for this period
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->get();

            $workingDays = $attendances->count();
            $presentDays = $attendances->where('status', 'Present')->count();
            $sickDays = $attendances->where('status', 'Sick')->count();
            $leaveDays = $attendances->where('status', 'Leave')->count();
            $overtimeHours = $attendances->sum('overtime_hours');

            // Calculate basic salary (pro-rated based on attendance)
            $dailyRate = $employee->basic_salary / 22;
            $basicSalary = $dailyRate * $presentDays;

            // Calculate allowances
            $allowances = $this->calculateAllowances($employee, $period);

            // Calculate overtime
            $hourlyRate = $employee->basic_salary / (22 * 8);
            $overtimePay = $overtimeHours * $hourlyRate * 1.5; // 1.5x rate

            $grossSalary = $basicSalary + $allowances + $overtimePay;

            // Calculate deductions
            $pph21 = $this->calculatePPh21($employee, $grossSalary);
            $bpjsKesehatan = min($employee->basic_salary, 12000000) * 0.01;
            $bpjsTK = min($employee->basic_salary, 8759400) * 0.02;
            $otherDeductions = $this->calculateOtherDeductions($employee, $period);

            $totalDeductions = $pph21 + $bpjsKesehatan + $bpjsTK + $otherDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            // Company BPJS contributions
            $bpjsKesehatanCompany = min($employee->basic_salary, 12000000) * 0.04;
            $bpjsTKCompany = min($employee->basic_salary, 8759400) * 0.037;

            // Create payroll detail
            PayrollDetail::updateOrCreate(
                [
                    'payroll_period_id' => $period->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'basic_salary' => $basicSalary,
                    'total_allowances' => $allowances + $overtimePay,
                    'total_deductions' => $totalDeductions,
                    'pph_21' => $pph21,
                    'bpjs_kesehatan_emp' => $bpjsKesehatan,
                    'bpjs_tk_emp' => $bpjsTK,
                    'bpjs_kesehatan_comp' => $bpjsKesehatanCompany,
                    'bpjs_tk_comp' => $bpjsTKCompany,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'sick_days' => $sickDays,
                    'leave_days' => $leaveDays,
                    'overtime_hours' => $overtimeHours,
                    'overtime_pay' => $overtimePay,
                    'calculation_details' => [
                        'daily_rate' => $dailyRate,
                        'hourly_rate' => $hourlyRate,
                        'allowances_breakdown' => $this->getAllowancesBreakdown($employee, $period),
                        'deductions_breakdown' => $this->getDeductionsBreakdown($employee, $period),
                    ],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $totalGrossSalary += $grossSalary;
            $totalNetSalary += $netSalary;
            $totalEmployees++;
        }

        // Update period totals
        $period->update([
            'total_employees' => $totalEmployees,
            'total_amount' => $totalNetSalary,
            'updated_at' => now(),
        ]);
    }

    private function getOrCreateSalaryComponent($componentData)
    {
        $component = DB::table('salary_components')
            ->where('name', $componentData['name'])
            ->where('type', $componentData['type'])
            ->first();

        if (!$component) {
            $componentId = DB::table('salary_components')->insertGetId([
                'name' => $componentData['name'],
                'type' => $componentData['type'],
                'description' => "Auto-generated {$componentData['name']}",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return $componentId;
        }

        return $component->id;
    }

    private function calculateAllowances($employee, $period)
    {
        $totalAllowances = 0;

        $components = EmployeeComponent::where('employee_id', $employee->id)
            ->where('effective_date', '<=', $period->end_date)
            ->where(function($query) use ($period) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $period->start_date);
            })
            ->get();

        foreach ($components as $component) {
            if ($component->is_percentage) {
                $totalAllowances += ($employee->basic_salary * $component->amount) / 100;
            } else {
                $totalAllowances += $component->amount;
            }
        }

        return $totalAllowances;
    }

    private function calculatePPh21($employee, $grossSalary)
    {
        // PTKP rates
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

        $ptkpAmount = $ptkpRates[$employee->ptkp_status] ?? 54000000;
        $jobDeduction = min($grossSalary * 0.05, 500000);

        $bpjsDeductions = min($employee->basic_salary, 12000000) * 0.01 +
                         min($employee->basic_salary, 8759400) * 0.02;

        $taxableIncome = $grossSalary - $jobDeduction - $bpjsDeductions;
        $annualTaxableIncome = ($taxableIncome * 12) - $ptkpAmount;

        if ($annualTaxableIncome <= 0) return 0;

        return $this->calculateProgressiveTax($annualTaxableIncome) / 12;
    }

    private function calculateProgressiveTax($annualIncome)
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

    private function calculateOtherDeductions($employee, $period)
    {
        $totalDeductions = 0;

        $deductions = Deduction::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where('start_date', '<=', $period->end_date)
            ->where(function($query) use ($period) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $period->start_date);
            })
            ->get();

        foreach ($deductions as $deduction) {
            // Calculate monthly amount from total amount and installment count
            $monthlyAmount = $deduction->installment_count > 0 ?
                $deduction->amount / $deduction->installment_count :
                $deduction->amount;
            $totalDeductions += $monthlyAmount;
        }

        return $totalDeductions;
    }

    private function getAllowancesBreakdown($employee, $period)
    {
        $breakdown = [];

        $components = EmployeeComponent::where('employee_id', $employee->id)
            ->where('effective_date', '<=', $period->end_date)
            ->where(function($query) use ($period) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $period->start_date);
            })
            ->get();

        foreach ($components as $component) {
            $amount = $component->is_percentage ?
                ($employee->basic_salary * $component->amount) / 100 :
                $component->amount;

            $breakdown[] = [
                'name' => $component->component->name,
                'amount' => $amount,
                'type' => $component->is_percentage ? 'percentage' : 'fixed'
            ];
        }

        return $breakdown;
    }

    private function getDeductionsBreakdown($employee, $period)
    {
        $breakdown = [];

        $deductions = Deduction::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where('start_date', '<=', $period->end_date)
            ->where(function($query) use ($period) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $period->start_date);
            })
            ->get();

        foreach ($deductions as $deduction) {
            $monthlyAmount = $deduction->installment_count > 0 ?
                $deduction->amount / $deduction->installment_count :
                $deduction->amount;
            $breakdown[] = [
                'name' => $deduction->type,
                'amount' => $monthlyAmount,
                'description' => $deduction->description
            ];
        }

        return $breakdown;
    }
}