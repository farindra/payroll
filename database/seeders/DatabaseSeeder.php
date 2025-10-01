<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            PayrollPeriodSeeder::class,
            ComprehensivePayrollSeeder::class,
            ShieldSeeder::class,  // This will create all Filament Shield permissions and roles
            PermissionSeeder::class,  // Custom permissions for payroll reports
            RoleSeeder::class,  // Custom role assignments (may be redundant now)
            UserSeeder::class,
        ]);
    }
}
