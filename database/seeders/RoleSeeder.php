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
        // Basic view permissions for most resources
        $employeePermissions = Permission::where(function($query) {
            $query->where('name', 'like', '%view_any')
                  ->orWhere('name', 'like', '%page_Dashboard')
                  ->orWhere('name', 'like', '%widget%')
                  ->orWhere('name', 'view_any_employee')
                  ->orWhere('name', 'view_employee')
                  ->orWhere('name', 'view_any_department')
                  ->orWhere('name', 'view_department')
                  ->orWhere('name', 'view_any_payroll_period')
                  ->orWhere('name', 'view_payroll_period')
                  ->orWhere('name', 'view_any_attendance')
                  ->orWhere('name', 'view_attendance')
                  ->orWhere('name', 'view_any_payroll_detail')
                  ->orWhere('name', 'view_payroll_detail');
        })->pluck('name')->toArray();

        // Remove admin-only permissions
        $excludedPermissions = [
            'user management access',
            'role management access',
            'view user',
            'create user',
            'edit user',
            'delete user',
            'view role',
            'create role',
            'edit role',
            'delete role',
        ];

        $employeePermissions = array_diff($employeePermissions, $excludedPermissions);

        $employeeRole->syncPermissions($employeePermissions);

        $this->command->info('Roles seeded successfully!');
        $this->command->info('Admin role has ' . $adminRole->permissions->count() . ' permissions');
        $this->command->info('Employee role has ' . $employeeRole->permissions->count() . ' permissions');
    }
}