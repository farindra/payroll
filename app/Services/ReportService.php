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
                'name' => 'Your Company Name',
                'address' => 'Company Address',
                'phone' => 'Company Phone',
            ],
        ]);

        return $pdf;
    }
}