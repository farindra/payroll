<?php

namespace App\Services;

use App\Models\PayrollPeriod;
use App\Models\PayrollDetail;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    public function generatePayrollSummary($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $payrollDetails = PayrollDetail::with(['employee.department'])
            ->where('payroll_period_id', $periodId)
            ->get();

        return [
            'period' => $period,
            'summary' => [
                'total_employees' => $payrollDetails->count(),
                'total_gross_salary' => $payrollDetails->sum('gross_salary'),
                'total_net_salary' => $payrollDetails->sum('net_salary'),
                'total_pph21' => $payrollDetails->sum('pph_21'),
                'total_bpjs_kesehatan' => $payrollDetails->sum('bpjs_kesehatan_emp'),
                'total_bpjs_tk' => $payrollDetails->sum('bpjs_tk_emp'),
                'total_allowances' => $payrollDetails->sum('total_allowances'),
                'total_deductions' => $payrollDetails->sum('total_deductions'),
            ],
            'department_summary' => $this->getDepartmentSummary($payrollDetails),
            'employees' => $payrollDetails,
        ];
    }

    public function generatePPh21Report($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $payrollDetails = PayrollDetail::with(['employee'])
            ->where('payroll_period_id', $periodId)
            ->get();

        // Calculate PTKP amounts
        $ptkpRates = [
            'TK/0' => 54000000,
            'TK/1' => 58500000,
            'TK/2' => 63000000,
            'TK/3' => 67500000,
            'K/0' => 54000000,
            'K/1' => 63000000,
            'K/2' => 67500000,
            'K/3' => 72000000,
        ];

        $reportData = $payrollDetails->map(function ($detail) use ($ptkpRates) {
            $ptkpAmount = $ptkpRates[$detail->employee->ptkp_status] ?? 54000000;
            $positionDeduction = min($detail->gross_salary * 0.05, 500000);
            $taxableIncome = max(0, $detail->gross_salary - $positionDeduction - $detail->bpjs_kesehatan_emp - $detail->bpjs_tk_emp - $ptkpAmount);

            return [
                'employee_id' => $detail->employee->nip,
                'employee_name' => $detail->employee->full_name,
                'npwp' => $detail->employee->npwp,
                'ptkp_status' => $detail->employee->ptkp_status,
                'gross_salary' => $detail->gross_salary,
                'pph_21' => $detail->pph_21,
                'ptkp_amount' => $ptkpAmount,
                'position_deduction' => $positionDeduction,
                'taxable_income' => $taxableIncome,
            ];
        });

        // Generate PTKP summary
        $ptkpSummary = $reportData->groupBy('ptkp_status')->map(function ($group) {
            return [
                'employee_count' => $group->count(),
                'total_pph21' => $group->sum('pph_21'),
                'average_pph21' => $group->avg('pph_21'),
            ];
        });

        return [
            'period' => $period,
            'summary' => [
                'total_employees' => $payrollDetails->count(),
                'total_gross_salary' => $payrollDetails->sum('gross_salary'),
                'total_ptkp' => $reportData->sum('ptkp_amount'),
                'total_pph21' => $payrollDetails->sum('pph_21'),
            ],
            'employees' => $payrollDetails,
            'ptkp_summary' => $ptkpSummary,
        ];
    }

    public function generateBPJSReport($periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $payrollDetails = PayrollDetail::with(['employee'])
            ->where('payroll_period_id', $periodId)
            ->get();

        $totalBpjsKesehatanEmployee = $payrollDetails->sum('bpjs_kesehatan_emp');
        $totalBpjsTkEmployee = $payrollDetails->sum('bpjs_tk_emp');
        $totalBpjsKesehatanCompany = $payrollDetails->sum('bpjs_kesehatan_comp');
        $totalBpjsTkCompany = $payrollDetails->sum('bpjs_tk_comp');

        return [
            'period' => $period,
            'summary' => [
                'total_employees' => $payrollDetails->count(),
                'total_bpjs_kesehatan' => $totalBpjsKesehatanEmployee + $totalBpjsKesehatanCompany,
                'total_bpjs_tk' => $totalBpjsTkEmployee + $totalBpjsTkCompany,
                'total_company_bpjs' => $totalBpjsKesehatanCompany + $totalBpjsTkCompany,
            ],
            'employees' => $payrollDetails,
            'company_summary' => [
                'bpjs_kesehatan_company' => $totalBpjsKesehatanCompany,
                'bpjs_tk_company' => $totalBpjsTkCompany,
            ],
        ];
    }

    public function generateAttendanceReport($startDate, $endDate, $departmentId = null)
    {
        $query = \App\Models\Attendance::with(['employee.department'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $attendances = $query->get();
        $department = $departmentId ? Department::find($departmentId) : null;

        // Group by employee for summary
        $employeeSummary = $attendances->groupBy('employee_id')->map(function ($employeeAttendances) {
            $totalDays = $employeeAttendances->count();
            $presentDays = $employeeAttendances->where('status', 'Present')->count();
            $sickDays = $employeeAttendances->where('status', 'Sick')->count();
            $leaveDays = $employeeAttendances->where('status', 'Leave')->count();
            $overtimeHours = $employeeAttendances->sum('overtime_hours');
            $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

            return [
                'employee' => $employeeAttendances->first()->employee,
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'sick_days' => $sickDays,
                'leave_days' => $leaveDays,
                'overtime_hours' => $overtimeHours,
                'attendance_rate' => $attendanceRate,
            ];
        });

        // Department summary
        $departmentSummary = $employeeSummary->groupBy('employee.department.name')->map(function ($deptEmployees) {
            return [
                'employee_count' => $deptEmployees->count(),
                'total_present' => $deptEmployees->sum('present_days') ?? 0,
                'total_overtime' => $deptEmployees->sum('overtime_hours') ?? 0,
                'avg_attendance_rate' => $deptEmployees->avg('attendance_rate') ?? 0,
            ];
        });

        return [
            'period' => [
                'start_date' => \Carbon\Carbon::parse($startDate),
                'end_date' => \Carbon\Carbon::parse($endDate),
            ],
            'department' => $department,
            'summary' => [
                'total_employees' => $employeeSummary->count(),
                'total_present' => $employeeSummary->sum('present_days') ?? 0,
                'total_overtime_hours' => $employeeSummary->sum('overtime_hours') ?? 0,
                'total_absent' => ($employeeSummary->sum('sick_days') ?? 0) + ($employeeSummary->sum('leave_days') ?? 0),
            ],
            'employees' => $employeeSummary,
            'department_summary' => $departmentSummary,
        ];
    }

    protected function getDepartmentSummary($payrollDetails)
    {
        return $payrollDetails->groupBy('employee.department.name')->map(function ($department) {
            return [
                'employee_count' => $department->count(),
                'total_gross_salary' => $department->sum('gross_salary'),
                'total_net_salary' => $department->sum('net_salary'),
                'total_pph21' => $department->sum('pph_21'),
            ];
        });
    }

    protected function getAttendanceSummary($attendances)
    {
        return [
            'total_records' => $attendances->count(),
            'by_status' => $attendances->groupBy('status')->map->count(),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
            'total_working_hours' => $attendances->sum('total_hours'),
        ];
    }

    public function exportToExcel($data, $type)
    {
        switch ($type) {
            case 'payroll_summary':
                return $this->exportPayrollSummaryExcel($data);
            case 'pph21':
                return $this->exportPPh21Excel($data);
            case 'bpjs':
                return $this->exportBPJSExcel($data);
            case 'attendance':
                return $this->exportAttendanceExcel($data);
            default:
                throw new \Exception('Unknown export type');
        }
    }

    protected function exportPayrollSummaryExcel($data)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Payroll Summary Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['period']->period_name);
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Summary section
        $sheet->setCellValue('A5', 'Summary');
        $sheet->setCellValue('A6', 'Total Employees');
        $sheet->setCellValue('B6', $data['summary']['total_employees']);
        $sheet->setCellValue('A7', 'Total Gross Salary');
        $sheet->setCellValue('B7', $data['summary']['total_gross_salary']);
        $sheet->setCellValue('A8', 'Total Net Salary');
        $sheet->setCellValue('B8', $data['summary']['total_net_salary']);
        $sheet->setCellValue('A9', 'Total PPh 21');
        $sheet->setCellValue('B9', $data['summary']['total_pph21']);

        // Employee details
        $sheet->setCellValue('A11', 'Employee Details');
        $sheet->setCellValue('A12', 'Employee ID');
        $sheet->setCellValue('B12', 'Name');
        $sheet->setCellValue('C12', 'Department');
        $sheet->setCellValue('D12', 'Basic Salary');
        $sheet->setCellValue('E12', 'Allowances');
        $sheet->setCellValue('F12', 'Gross Salary');
        $sheet->setCellValue('G12', 'Deductions');
        $sheet->setCellValue('H12', 'PPh 21');
        $sheet->setCellValue('I12', 'Net Salary');

        $row = 13;
        foreach ($data['employees'] as $employee) {
            $sheet->setCellValue('A' . $row, $employee->employee->nip);
            $sheet->setCellValue('B' . $row, $employee->employee->full_name);
            $sheet->setCellValue('C' . $row, $employee->employee->department->name);
            $sheet->setCellValue('D' . $row, $employee->basic_salary);
            $sheet->setCellValue('E' . $row, $employee->total_allowances);
            $sheet->setCellValue('F' . $row, $employee->gross_salary);
            $sheet->setCellValue('G' . $row, $employee->total_deductions);
            $sheet->setCellValue('H' . $row, $employee->pph_21);
            $sheet->setCellValue('I' . $row, $employee->net_salary);
            $row++;
        }

        return $spreadsheet;
    }

    public function generatePayslipPDF($payrollDetailId)
    {
        $payrollDetail = PayrollDetail::with(['employee.department', 'payrollPeriod'])
            ->findOrFail($payrollDetailId);

        $pdf = PDF::loadView('reports.payslip', [
            'payrollDetail' => $payrollDetail,
            'company' => [
                'name' => config('app.company_name', 'PT. Payroll System Indonesia'),
                'address' => config('app.company_address', 'Jl. Teknologi No. 123, Jakarta Selatan 12345'),
                'phone' => config('app.company_phone', '+62 21 1234 5678'),
                'email' => config('app.company_email', 'hrd@payrollsystem.co.id'),
                'website' => config('app.company_website', 'www.payrollsystem.co.id'),
            ],
        ]);

        return $pdf;
    }

    public function generateEmployeePayrollSummary($periodId, $employeeId)
    {
        $utf8Cleaner = new Utf8CleanerService();
        $period = PayrollPeriod::findOrFail($periodId);
        $payrollDetail = PayrollDetail::with(['employee.department'])
            ->where('payroll_period_id', $periodId)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$payrollDetail) {
            throw new \Exception('Payroll data not found for this employee in the selected period.');
        }

        // Clean period name to prevent UTF-8 issues
        $period->period_name = $utf8Cleaner->cleanString($period->period_name);

        // Use actual component data from calculation details with aggressive UTF-8 cleaning
        $earnings = [];
        try {
            $dynamicEarnings = $payrollDetail->earnings;
            if (empty($dynamicEarnings)) {
                // Fallback to original structure if no dynamic earnings
                $earnings = [
                    ['name' => 'Gaji Pokok', 'amount' => $payrollDetail->basic_salary, 'type' => 'basic_salary'],
                    ['name' => 'Tunjangan', 'amount' => $payrollDetail->total_allowances, 'type' => 'allowances'],
                ];
            } else {
                // Process dynamic earnings with aggressive UTF-8 cleaning
                foreach ($dynamicEarnings as $earning) {
                    if (isset($earning['name']) && isset($earning['amount'])) {
                        $earnings[] = [
                            'name' => $utf8Cleaner->cleanString($earning['name']),
                            'amount' => $earning['amount'],
                            'type' => $earning['type'] ?? 'other',
                            'is_percentage' => $earning['is_percentage'] ?? false,
                            'percentage' => $earning['percentage'] ?? null,
                            'component_id' => $earning['component_id'] ?? null
                        ];
                    }
                }

                // Add basic salary as a separate component if it exists and not already included
                if ($payrollDetail->basic_salary > 0) {
                    $hasBasicSalary = collect($earnings)->contains(function($item) {
                        return $item['type'] === 'basic_salary' || str_contains(strtolower($item['name']), 'gaji pokok');
                    });
                    if (!$hasBasicSalary) {
                        array_unshift($earnings, [
                            'name' => 'Gaji Pokok',
                            'amount' => $payrollDetail->basic_salary,
                            'type' => 'basic_salary',
                            'is_percentage' => false,
                            'percentage' => null,
                            'component_id' => null
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback to basic structure on any error
            $earnings = [
                ['name' => 'Gaji Pokok', 'amount' => $payrollDetail->basic_salary, 'type' => 'basic_salary'],
                ['name' => 'Tunjangan', 'amount' => $payrollDetail->total_allowances, 'type' => 'allowances'],
            ];
        }

        $deductions = [];
        try {
            $dynamicDeductions = $payrollDetail->deductions;
            if (empty($dynamicDeductions)) {
                // Fallback to original structure if no dynamic deductions
                if ($payrollDetail->pph_21 > 0) {
                    $deductions[] = ['name' => 'PPH 21', 'amount' => $payrollDetail->pph_21, 'type' => 'tax'];
                }
                if ($payrollDetail->bpjs_kesehatan_emp > 0) {
                    $deductions[] = ['name' => 'BPJS Kesehatan', 'amount' => $payrollDetail->bpjs_kesehatan_emp, 'type' => 'insurance'];
                }
                if ($payrollDetail->bpjs_tk_emp > 0) {
                    $deductions[] = ['name' => 'BPJS Ketenagakerjaan', 'amount' => $payrollDetail->bpjs_tk_emp, 'type' => 'insurance'];
                }
            } else {
                // Process dynamic deductions with aggressive UTF-8 cleaning
                foreach ($dynamicDeductions as $deduction) {
                    if (isset($deduction['name']) && isset($deduction['amount'])) {
                        $deductions[] = [
                            'name' => $utf8Cleaner->cleanString($deduction['name']),
                            'amount' => $deduction['amount'],
                            'type' => $deduction['type'] ?? 'other',
                            'is_percentage' => $deduction['is_percentage'] ?? false,
                            'percentage' => $deduction['percentage'] ?? null,
                            'component_id' => $deduction['component_id'] ?? null
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback to basic structure on any error
            $deductions = [];
            if ($payrollDetail->pph_21 > 0) {
                $deductions[] = ['name' => 'PPH 21', 'amount' => $payrollDetail->pph_21, 'type' => 'tax'];
            }
            if ($payrollDetail->bpjs_kesehatan_emp > 0) {
                $deductions[] = ['name' => 'BPJS Kesehatan', 'amount' => $payrollDetail->bpjs_kesehatan_emp, 'type' => 'insurance'];
            }
            if ($payrollDetail->bpjs_tk_emp > 0) {
                $deductions[] = ['name' => 'BPJS Ketenagakerjaan', 'amount' => $payrollDetail->bpjs_tk_emp, 'type' => 'insurance'];
            }
        }

        return [
            'period' => [
                'period_name' => $utf8Cleaner->cleanString($period->period_name),
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'status' => $period->status,
            ],
            'employee' => [
                'id' => $payrollDetail->employee->id,
                'full_name' => $utf8Cleaner->cleanString($payrollDetail->employee->full_name),
                'nip' => $payrollDetail->employee->nip,
                'department' => $utf8Cleaner->cleanString($payrollDetail->employee->department->name ?? null),
                'position' => $utf8Cleaner->cleanString($payrollDetail->employee->position),
            ],
            'payroll_detail' => [
                'earnings' => array_filter($earnings, fn($item) => $item['amount'] > 0),
                'deductions' => array_filter($deductions, fn($item) => $item['amount'] > 0),
                'take_home_pay' => $payrollDetail->net_salary,
                'gross_salary' => $payrollDetail->gross_salary,
                'net_salary' => $payrollDetail->net_salary,
            ],
        ];
    }

    public function generateEmployeeAttendanceReport($startDate, $endDate, $employeeId)
    {
        $attendances = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $summary = [
            'present' => $attendances->where('status', 'Present')->count(),
            'late' => $attendances->where('status', 'Late')->count(),
            'absent' => $attendances->where('status', 'Absent')->count(),
            'leave' => $attendances->where('status', 'Leave')->count() + $attendances->where('status', 'Sick')->count(),
        ];

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'attendances' => $attendances->map(function ($attendance) {
                return [
                    'date' => $attendance->date,
                    'check_in' => $attendance->clock_in,
                    'check_out' => $attendance->clock_out,
                    'status' => strtolower($attendance->status),
                    'notes' => $attendance->note,
                ];
            }),
        ];
    }

    public function exportEmployeeReportToExcel($data, $type)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        switch ($type) {
            case 'payroll_summary':
                return $this->exportEmployeePayrollSummaryExcel($data);
            case 'attendance':
                return $this->exportEmployeeAttendanceExcel($data);
            default:
                throw new \Exception('Unknown export type');
        }
    }

    protected function exportEmployeePayrollSummaryExcel($data)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Laporan Penggajian Pribadi');
        $sheet->setCellValue('A2', 'Nama: ' . $data['employee']['name']);
        $sheet->setCellValue('A3', 'NIK: ' . $data['employee']['nik']);
        $sheet->setCellValue('A4', 'Periode: ' . $data['period']['period_name']);
        $sheet->setCellValue('A5', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Employee info
        $sheet->setCellValue('A7', 'Informasi Karyawan');
        $sheet->setCellValue('A8', 'Departemen');
        $sheet->setCellValue('B8', $data['employee']['department'] ?? '-');
        $sheet->setCellValue('A9', 'Jabatan');
        $sheet->setCellValue('B9', $data['employee']['position'] ?? '-');

        // Earnings
        $row = 11;
        $sheet->setCellValue('A' . $row, 'Pendapatan');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($data['payroll_detail']['earnings'] as $earning) {
            $sheet->setCellValue('A' . $row, $earning['name']);
            $sheet->setCellValue('B' . $row, $earning['amount']);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Deductions
        $row++;
        $sheet->setCellValue('A' . $row, 'Potongan');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($data['payroll_detail']['deductions'] as $deduction) {
            $sheet->setCellValue('A' . $row, $deduction['name']);
            $sheet->setCellValue('B' . $row, $deduction['amount']);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Net salary
        $row++;
        $sheet->setCellValue('A' . $row, 'Take Home Pay');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $row, $data['payroll_detail']['take_home_pay']);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);

        return $spreadsheet;
    }

    protected function exportEmployeeAttendanceExcel($data)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Laporan Kehadiran Pribadi');
        $sheet->setCellValue('A2', 'Periode: ' . $data['period']['start_date'] . ' - ' . $data['period']['end_date']);
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Summary
        $sheet->setCellValue('A5', 'Ringkasan');
        $sheet->setCellValue('A6', 'Hadir');
        $sheet->setCellValue('B6', $data['summary']['present']);
        $sheet->setCellValue('A7', 'Terlambat');
        $sheet->setCellValue('B7', $data['summary']['late']);
        $sheet->setCellValue('A8', 'Tidak Hadir');
        $sheet->setCellValue('B8', $data['summary']['absent']);
        $sheet->setCellValue('A9', 'Cuti');
        $sheet->setCellValue('B9', $data['summary']['leave']);

        // Attendance details
        $row = 11;
        $sheet->setCellValue('A' . $row, 'Tanggal');
        $sheet->setCellValue('B' . $row, 'Check In');
        $sheet->setCellValue('C' . $row, 'Check Out');
        $sheet->setCellValue('D' . $row, 'Status');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($data['attendances'] as $attendance) {
            $sheet->setCellValue('A' . $row, $attendance['date']);
            $sheet->setCellValue('B' . $row, $attendance['check_in'] ?? '-');
            $sheet->setCellValue('C' . $row, $attendance['check_out'] ?? '-');
            $sheet->setCellValue('D' . $row, $attendance['status']);
            $row++;
        }

        return $spreadsheet;
    }
}