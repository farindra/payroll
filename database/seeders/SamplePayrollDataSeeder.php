<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SamplePayrollDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, let's update some existing periods to "Calculated" status
        DB::table('payroll_periods')
            ->where('period_name', 'September 2024')
            ->update([
                'status' => 'Calculated',
                'total_employees' => 5,
                'total_amount' => 45000000,
                'updated_at' => Carbon::now()
            ]);

        // Add sample payroll details for September 2024
        $payrollDetails = [
            [
                'payroll_period_id' => 1, // Assuming September 2024 has ID 1
                'employee_id' => 1, // Ahmad Wijaya
                'basic_salary' => 8000000,
                'gross_salary' => 8500000,
                'net_salary' => 7200000,
                'pph_21' => 800000,
                'bpjs_kesehatan_emp' => 80000,
                'bpjs_kesehatan_comp' => 80000,
                'bpjs_tk_emp' => 240000,
                'bpjs_tk_comp' => 360000,
                'working_days' => 22,
                'present_days' => 20,
                'absent_days' => 1,
                'sick_days' => 1,
                'leave_days' => 0,
                'overtime_hours' => 5,
                'overtime_pay' => 500000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'payroll_period_id' => 1,
                'employee_id' => 2, // Siti Rahayu
                'basic_salary' => 10000000,
                'gross_salary' => 10500000,
                'net_salary' => 8900000,
                'pph_21' => 1200000,
                'bpjs_kesehatan_emp' => 100000,
                'bpjs_kesehatan_comp' => 100000,
                'bpjs_tk_emp' => 300000,
                'bpjs_tk_comp' => 450000,
                'working_days' => 22,
                'present_days' => 22,
                'absent_days' => 0,
                'sick_days' => 0,
                'leave_days' => 0,
                'overtime_hours' => 3,
                'overtime_pay' => 450000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'payroll_period_id' => 1,
                'employee_id' => 3, // Budi Santoso
                'basic_salary' => 7500000,
                'gross_salary' => 7800000,
                'net_salary' => 6600000,
                'pph_21' => 750000,
                'bpjs_kesehatan_emp' => 75000,
                'bpjs_kesehatan_comp' => 75000,
                'bpjs_tk_emp' => 225000,
                'bpjs_tk_comp' => 337500,
                'working_days' => 22,
                'present_days' => 21,
                'absent_days' => 1,
                'sick_days' => 0,
                'leave_days' => 0,
                'overtime_hours' => 2,
                'overtime_pay' => 300000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'payroll_period_id' => 1,
                'employee_id' => 4, // Dewi Lestari
                'basic_salary' => 7000000,
                'gross_salary' => 7200000,
                'net_salary' => 6100000,
                'pph_21' => 700000,
                'bpjs_kesehatan_emp' => 70000,
                'bpjs_kesehatan_comp' => 70000,
                'bpjs_tk_emp' => 210000,
                'bpjs_tk_comp' => 315000,
                'working_days' => 22,
                'present_days' => 22,
                'absent_days' => 0,
                'sick_days' => 0,
                'leave_days' => 0,
                'overtime_hours' => 0,
                'overtime_pay' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'payroll_period_id' => 1,
                'employee_id' => 5, // Eko Prasetyo
                'basic_salary' => 12000000,
                'gross_salary' => 12500000,
                'net_salary' => 10500000,
                'pph_21' => 1500000,
                'bpjs_kesehatan_emp' => 120000,
                'bpjs_kesehatan_comp' => 120000,
                'bpjs_tk_emp' => 360000,
                'bpjs_tk_comp' => 540000,
                'working_days' => 22,
                'present_days' => 20,
                'absent_days' => 0,
                'sick_days' => 1,
                'leave_days' => 1,
                'overtime_hours' => 8,
                'overtime_pay' => 1200000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert payroll details
        foreach ($payrollDetails as $detail) {
            $exists = DB::table('payroll_details')
                ->where('payroll_period_id', $detail['payroll_period_id'])
                ->where('employee_id', $detail['employee_id'])
                ->exists();

            if (!$exists) {
                DB::table('payroll_details')->insert($detail);
            }
        }

        // Also create a September 2025 period that's calculated
        $september2025Exists = DB::table('payroll_periods')
            ->where('period_name', 'September 2025')
            ->exists();

        if (!$september2025Exists) {
            $september2025Id = DB::table('payroll_periods')->insertGetId([
                'period_name' => 'September 2025',
                'start_date' => '2025-09-01',
                'end_date' => '2025-09-30',
                'payment_date' => '2025-10-05',
                'status' => 'Calculated',
                'total_employees' => 5,
                'total_amount' => 39300000,
                'notes' => 'Monthly payroll for September 2025',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Add payroll details for September 2025 as well
            $sep2025Details = [
                [
                    'payroll_period_id' => $september2025Id,
                    'employee_id' => 1,
                    'basic_salary' => 8500000,
                    'gross_salary' => 9000000,
                    'net_salary' => 7650000,
                    'pph_21' => 850000,
                    'bpjs_kesehatan_emp' => 85000,
                    'bpjs_kesehatan_comp' => 85000,
                    'bpjs_tk_emp' => 255000,
                    'bpjs_tk_comp' => 382500,
                    'working_days' => 22,
                    'present_days' => 22,
                    'absent_days' => 0,
                    'sick_days' => 0,
                    'leave_days' => 0,
                    'overtime_hours' => 4,
                    'overtime_pay' => 600000,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'payroll_period_id' => $september2025Id,
                    'employee_id' => 2,
                    'basic_salary' => 10500000,
                    'gross_salary' => 11000000,
                    'net_salary' => 9350000,
                    'pph_21' => 1250000,
                    'bpjs_kesehatan_emp' => 105000,
                    'bpjs_kesehatan_comp' => 105000,
                    'bpjs_tk_emp' => 315000,
                    'bpjs_tk_comp' => 472500,
                    'working_days' => 22,
                    'present_days' => 21,
                    'absent_days' => 0,
                    'sick_days' => 1,
                    'leave_days' => 0,
                    'overtime_hours' => 5,
                    'overtime_pay' => 750000,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'payroll_period_id' => $september2025Id,
                    'employee_id' => 3,
                    'basic_salary' => 7800000,
                    'gross_salary' => 8100000,
                    'net_salary' => 6885000,
                    'pph_21' => 780000,
                    'bpjs_kesehatan_emp' => 78000,
                    'bpjs_kesehatan_comp' => 78000,
                    'bpjs_tk_emp' => 234000,
                    'bpjs_tk_comp' => 351000,
                    'working_days' => 22,
                    'present_days' => 20,
                    'absent_days' => 1,
                    'sick_days' => 0,
                    'leave_days' => 1,
                    'overtime_hours' => 3,
                    'overtime_pay' => 450000,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'payroll_period_id' => $september2025Id,
                    'employee_id' => 4,
                    'basic_salary' => 7200000,
                    'gross_salary' => 7400000,
                    'net_salary' => 6290000,
                    'pph_21' => 720000,
                    'bpjs_kesehatan_emp' => 72000,
                    'bpjs_kesehatan_comp' => 72000,
                    'bpjs_tk_emp' => 216000,
                    'bpjs_tk_comp' => 324000,
                    'working_days' => 22,
                    'present_days' => 22,
                    'absent_days' => 0,
                    'sick_days' => 0,
                    'leave_days' => 0,
                    'overtime_hours' => 0,
                    'overtime_pay' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'payroll_period_id' => $september2025Id,
                    'employee_id' => 5,
                    'basic_salary' => 12500000,
                    'gross_salary' => 13000000,
                    'net_salary' => 11050000,
                    'pph_21' => 1550000,
                    'bpjs_kesehatan_emp' => 125000,
                    'bpjs_kesehatan_comp' => 125000,
                    'bpjs_tk_emp' => 375000,
                    'bpjs_tk_comp' => 562500,
                    'working_days' => 22,
                    'present_days' => 21,
                    'absent_days' => 0,
                    'sick_days' => 0,
                    'leave_days' => 1,
                    'overtime_hours' => 6,
                    'overtime_pay' => 900000,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ];

            foreach ($sep2025Details as $detail) {
                DB::table('payroll_details')->insert($detail);
            }
        }

        $this->command->info('Sample payroll data seeded successfully!');
    }
}
