<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // User permissions
        Permission::firstOrCreate(['name' => 'user management access', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view user', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create user', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit user', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete user', 'guard_name' => 'web']);

        // Role permissions
        Permission::firstOrCreate(['name' => 'role management access', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view role', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create role', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit role', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete role', 'guard_name' => 'web']);

        // Department permissions
        Permission::firstOrCreate(['name' => 'view_any_department', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_department', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create department', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit department', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete department', 'guard_name' => 'web']);

        // Employee permissions
        Permission::firstOrCreate(['name' => 'view_any_employee', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_employee', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create employee', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit employee', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete employee', 'guard_name' => 'web']);

        // Payroll Period permissions
        Permission::firstOrCreate(['name' => 'view_any_payroll_period', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_payroll_period', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create payroll_period', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit payroll_period', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete payroll_period', 'guard_name' => 'web']);

        // Attendance permissions
        Permission::firstOrCreate(['name' => 'view_any_attendance', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_attendance', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create attendance', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit attendance', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete attendance', 'guard_name' => 'web']);

        // Payroll Detail permissions
        Permission::firstOrCreate(['name' => 'view_any_payroll_detail', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_payroll_detail', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create payroll_detail', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit payroll_detail', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete payroll_detail', 'guard_name' => 'web']);

        // Dashboard permissions
        Permission::firstOrCreate(['name' => 'page_Dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'widget_Statistics', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'widget_RecentActivity', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'widget_AccountWidget', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'widget_FilamentInfoWidget', 'guard_name' => 'web']);

        // Panel access
        Permission::firstOrCreate(['name' => 'panel_access', 'guard_name' => 'web']);

        // Employee-specific page permissions
        Permission::firstOrCreate(['name' => 'page_EmployeeDashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'page_EmployeePayrollReports', 'guard_name' => 'web']);

        // Report permissions (for employee payroll reports)
        Permission::firstOrCreate(['name' => 'view_any_employee_payroll_reports', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_employee_payroll_reports', 'guard_name' => 'web']);

        $this->command->info('Permissions seeded successfully!');
    }
}