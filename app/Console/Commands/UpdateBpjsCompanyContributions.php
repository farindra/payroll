<?php

namespace App\Console\Commands;

use App\Models\PayrollDetail;
use Illuminate\Console\Command;

class UpdateBpjsCompanyContributions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payroll:update-bpjs-company-contributions';

    /**
     * The console command description.
     */
    protected $description = 'Update BPJS company contributions for existing payroll records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating BPJS company contributions...');

        $payrollDetails = PayrollDetail::whereNull('bpjs_kesehatan_comp')
            ->orWhereNull('bpjs_tk_comp')
            ->get();

        $updatedCount = 0;

        foreach ($payrollDetails as $payrollDetail) {
            // Calculate BPJS Kesehatan company (4% of salary, max 12 juta)
            $bpjsKesehatanSalary = min($payrollDetail->basic_salary, 12000000);
            $bpjsKesehatanCompany = $bpjsKesehatanSalary * 0.04;

            // Calculate BPJS TK company (3.7% of salary, max 8,759,400)
            $bpjsTkSalary = min($payrollDetail->basic_salary, 8759400);
            $bpjsTkCompany = $bpjsTkSalary * 0.037;

            $payrollDetail->update([
                'bpjs_kesehatan_comp' => $bpjsKesehatanCompany,
                'bpjs_tk_comp' => $bpjsTkCompany,
            ]);

            $updatedCount++;
        }

        $this->info("Updated {$updatedCount} payroll records with BPJS company contributions.");
        $this->info('BPJS company contributions update completed successfully.');

        return 0;
    }
}
