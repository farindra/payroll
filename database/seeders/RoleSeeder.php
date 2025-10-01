<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create admin role with all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        // Create employee role with limited permissions
        $employeeRole = Role::firstOrCreate([
            'name' => 'employee',
            'guard_name' => 'web'
        ]);

        // Get all permissions
        $allPermissions = Permission::all();

        // Give all permissions to admin
        $adminRole->syncPermissions($allPermissions);

        // Give limited permissions to employee
        // Only essential permissions for employee role
        $employeePermissions = [
            'view_any_attendance',
            'view_attendance',
            'view_any_payroll_detail',
            'view_payroll_detail',
            'page_Dashboard',
            'panel_access',
            'widget_AccountWidget',
            'widget_FilamentInfoWidget',
            'page_EmployeeDashboard',
            'page_EmployeePayrollReports',
        ];

        $employeeRole->syncPermissions($employeePermissions);

        if ($this->command) {
            $this->command->info('Roles seeded successfully!');
            $this->command->info('Admin role has ' . $adminRole->permissions->count() . ' permissions');
            $this->command->info('Employee role has ' . $employeeRole->permissions->count() . ' permissions');
        }
    }
}