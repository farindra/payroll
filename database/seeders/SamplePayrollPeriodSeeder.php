<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SamplePayrollPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periods = [
            [
                'period_name' => 'September 2024',
                'start_date' => '2024-09-01',
                'end_date' => '2024-09-30',
                'payment_date' => '2024-10-05',
                'status' => 'Draft',
                'total_employees' => 0,
                'total_amount' => 0,
                'notes' => 'Monthly payroll for September 2024',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'period_name' => 'October 2024',
                'start_date' => '2024-10-01',
                'end_date' => '2024-10-31',
                'payment_date' => '2024-11-05',
                'status' => 'Draft',
                'total_employees' => 0,
                'total_amount' => 0,
                'notes' => 'Monthly payroll for October 2024',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'period_name' => 'August 2024',
                'start_date' => '2024-08-01',
                'end_date' => '2024-08-31',
                'payment_date' => '2024-09-05',
                'status' => 'Calculated',
                'total_employees' => 5,
                'total_amount' => 45000000,
                'notes' => 'Monthly payroll for August 2024 - Already processed',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Check if periods already exist
        foreach ($periods as $period) {
            $exists = DB::table('payroll_periods')->where('period_name', $period['period_name'])->exists();
            if (!$exists) {
                DB::table('payroll_periods')->insert($period);
            }
        }

        $this->command->info('Sample payroll periods seeded successfully!');
    }
}
