<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'description' => 'HR Department',
                'budget' => 500000000,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance and Accounting',
                'budget' => 750000000,
            ],
            [
                'name' => 'Information Technology',
                'description' => 'IT Department',
                'budget' => 1000000000,
            ],
            [
                'name' => 'Operations',
                'description' => 'Operations Department',
                'budget' => 600000000,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Marketing and Sales',
                'budget' => 400000000,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}