<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Create or get admin user and employee
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@farindra.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        $adminEmployee = Employee::create([
            'nip' => 'EMP001',
            'full_name' => 'Admin User',
            'email' => 'admin@farindra.com',
            'position' => 'System Administrator',
            'department_id' => 1, // HR
            'basic_salary' => 15000000,
            'hire_date' => '2023-01-01',
            'employment_status' => 'active',
            'ptkp_status' => 'TK/0',
            'country' => 'Indonesia',
        ]);

        $adminUser->employee_id = $adminEmployee->id;
        $adminUser->save();

        // Create sample employees
        $employees = [
            [
                'nip' => 'EMP002',
                'full_name' => 'John Doe',
                'email' => 'john.doe@company.com',
                'position' => 'Software Developer',
                'department_id' => 3, // IT
                'basic_salary' => 12000000,
                'hire_date' => '2023-03-15',
                'ptkp_status' => 'TK/0',
            ],
            [
                'nip' => 'EMP003',
                'full_name' => 'Jane Smith',
                'email' => 'jane.smith@company.com',
                'position' => 'Finance Manager',
                'department_id' => 2, // Finance
                'basic_salary' => 18000000,
                'hire_date' => '2022-06-01',
                'ptkp_status' => 'K/2',
            ],
            [
                'nip' => 'EMP004',
                'full_name' => 'Mike Johnson',
                'email' => 'mike.johnson@company.com',
                'position' => 'HR Specialist',
                'department_id' => 1, // HR
                'basic_salary' => 10000000,
                'hire_date' => '2023-09-10',
                'ptkp_status' => 'TK/1',
            ],
            [
                'nip' => 'EMP005',
                'full_name' => 'Sarah Williams',
                'email' => 'sarah.williams@company.com',
                'position' => 'Marketing Executive',
                'department_id' => 5, // Marketing
                'basic_salary' => 9000000,
                'hire_date' => '2023-11-20',
                'ptkp_status' => 'TK/0',
            ],
        ];

        foreach ($employees as $employeeData) {
            $employee = Employee::create(array_merge($employeeData, [
                'employment_status' => 'active',
                'country' => 'Indonesia',
            ]));

            // Create corresponding user if not exists
            User::firstOrCreate(
                ['email' => $employeeData['email']],
                [
                    'name' => $employeeData['full_name'],
                    'password' => Hash::make('password'),
                    'employee_id' => $employee->id,
                ]
            );
        }
    }
}