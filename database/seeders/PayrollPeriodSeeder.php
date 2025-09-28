<?php

namespace Database\Seeders;

use App\Models\PayrollPeriod;
use Illuminate\Database\Seeder;

class PayrollPeriodSeeder extends Seeder
{
    public function run(): void
    {
        // Create payroll periods for the last 3 months
        $periods = [
            [
                'period_name' => 'July 2025',
                'start_date' => '2025-07-01',
                'end_date' => '2025-07-31',
                'payment_date' => '2025-08-01',
                'status' => 'Draft',
            ],
            [
                'period_name' => 'August 2025',
                'start_date' => '2025-08-01',
                'end_date' => '2025-08-31',
                'payment_date' => '2025-09-01',
                'status' => 'Draft',
            ],
            [
                'period_name' => 'September 2025',
                'start_date' => '2025-09-01',
                'end_date' => '2025-09-30',
                'payment_date' => '2025-10-01',
                'status' => 'Draft',
            ],
        ];

        foreach ($periods as $period) {
            PayrollPeriod::create($period);
        }
    }
}