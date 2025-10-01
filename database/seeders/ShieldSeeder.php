<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"admin","guard_name":"web","permissions":["user management access","create user","edit user","delete user","role management access","view_any_department","view_department","create_department","update_department","delete_department","delete_any_department","force_delete_department","force_delete_any_department","restore_department","restore_any_department","replicate_department","reorder_department","view_any_employee","view_employee","create_employee","update_employee","delete_employee","delete_any_employee","force_delete_employee","force_delete_any_employee","restore_employee","restore_any_employee","replicate_employee","reorder_employee","view_any_payrollperiod","view_payrollperiod","create_payrollperiod","update_payrollperiod","delete_payrollperiod","delete_any_payrollperiod","force_delete_payrollperiod","force_delete_any_payrollperiod","restore_payrollperiod","restore_any_payrollperiod","replicate_payrollperiod","reorder_payrollperiod","view_any_salarycomponent","view_salarycomponent","create_salarycomponent","update_salarycomponent","delete_salarycomponent","delete_any_salarycomponent","force_delete_salarycomponent","force_delete_any_salarycomponent","restore_salarycomponent","restore_any_salarycomponent","replicate_salarycomponent","reorder_salarycomponent","panel_access","page_Dashboard","widget_AccountWidget","widget_FilamentInfoWidget","view_any_role","view_role","create_role","update_role","delete_role","delete_any_role","force_delete_role","force_delete_any_role","restore_role","restore_any_role","replicate_role","reorder_role","view_any_permission","view_permission","create_permission","update_permission","delete_permission","delete_any_permission","force_delete_permission","force_delete_any_permission","restore_permission","restore_any_permission","replicate_permission","reorder_permission","view_payroll::period","view_any_payroll::period","create_payroll::period","update_payroll::period","restore_payroll::period","restore_any_payroll::period","replicate_payroll::period","reorder_payroll::period","delete_payroll::period","delete_any_payroll::period","force_delete_payroll::period","force_delete_any_payroll::period","view_salary::component","view_any_salary::component","create_salary::component","update_salary::component","restore_salary::component","restore_any_salary::component","replicate_salary::component","reorder_salary::component","delete_salary::component","delete_any_salary::component","force_delete_salary::component","force_delete_any_salary::component"]},{"name":"employee","guard_name":"web","permissions":["view_any_attendance","view_attendance","view_any_payroll_detail","view_payroll_detail","page_Dashboard","panel_access","widget_AccountWidget","widget_FilamentInfoWidget","page_EmployeeDashboard","page_EmployeePayrollReports"]},{"name":"super_admin","guard_name":"web","permissions":["user management access","create user","edit user","delete user","role management access","view_any_department","view_department","create_department","update_department","delete_department","delete_any_department","force_delete_department","force_delete_any_department","restore_department","restore_any_department","replicate_department","reorder_department","view_any_employee","view_employee","create_employee","update_employee","delete_employee","delete_any_employee","force_delete_employee","force_delete_any_employee","restore_employee","restore_any_employee","replicate_employee","reorder_employee","view_any_payrollperiod","view_payrollperiod","create_payrollperiod","update_payrollperiod","delete_payrollperiod","delete_any_payrollperiod","force_delete_payrollperiod","force_delete_any_payrollperiod","restore_payrollperiod","restore_any_payrollperiod","replicate_payrollperiod","reorder_payrollperiod","view_any_salarycomponent","view_salarycomponent","create_salarycomponent","update_salarycomponent","delete_salarycomponent","delete_any_salarycomponent","force_delete_salarycomponent","force_delete_any_salarycomponent","restore_salarycomponent","restore_any_salarycomponent","replicate_salarycomponent","reorder_salarycomponent","view_any_user","view_user","create_user","update_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","restore_user","restore_any_user","replicate_user","reorder_user","panel_access","page_Dashboard","widget_AccountWidget","widget_FilamentInfoWidget","view_any_role","view_role","create_role","update_role","delete_role","delete_any_role","force_delete_role","force_delete_any_role","restore_role","restore_any_role","replicate_role","reorder_role","view_any_permission","view_permission","create_permission","update_permission","delete_permission","delete_any_permission","force_delete_permission","force_delete_any_permission","restore_permission","restore_any_permission","replicate_permission","reorder_permission","view_payroll::period","view_any_payroll::period","create_payroll::period","update_payroll::period","restore_payroll::period","restore_any_payroll::period","replicate_payroll::period","reorder_payroll::period","delete_payroll::period","delete_any_payroll::period","force_delete_payroll::period","force_delete_any_payroll::period","view_salary::component","view_any_salary::component","create_salary::component","update_salary::component","restore_salary::component","restore_any_salary::component","replicate_salary::component","reorder_salary::component","delete_salary::component","delete_any_salary::component","force_delete_salary::component","force_delete_any_salary::component","page_EmployeeDashboard","page_EmployeePayrollReports","page_ImportAttendance","page_PayrollReports","page_ProcessPayroll","widget_EmployeeCountWidget","widget_DepartmentCountWidget"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        if ($this->command) {
            $this->command->info('Shield Seeding Completed.');
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
