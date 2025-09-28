<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'nip' => 'EMP001',
                'full_name' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@company.com',
                'phone' => '081234567890',
                'date_of_birth' => '1990-05-15',
                'gender' => 'Male',
                'marital_status' => 'Single',
                'nationality' => 'Indonesia',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '12345',
                'country' => 'Indonesia',
                'hire_date' => '2020-01-15',
                'employment_status' => 'active',
                'position' => 'Software Developer',
                'basic_salary' => 8000000,
                'npwp' => '1234567890123456',
                'ptkp_status' => 'TK/0',
                'bpjs_kesehatan_no' => '1234567890',
                'bpjs_tk_no' => '12345678901',
                'department_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nip' => 'EMP002',
                'full_name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@company.com',
                'phone' => '081234567891',
                'date_of_birth' => '1988-08-20',
                'gender' => 'Female',
                'marital_status' => 'Married',
                'nationality' => 'Indonesia',
                'address' => 'Jl. Thamrin No. 456, Jakarta',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '12346',
                'country' => 'Indonesia',
                'hire_date' => '2019-03-20',
                'employment_status' => 'active',
                'position' => 'HR Manager',
                'basic_salary' => 10000000,
                'npwp' => '1234567890123457',
                'ptkp_status' => 'TK/1',
                'bpjs_kesehatan_no' => '1234567891',
                'bpjs_tk_no' => '12345678902',
                'department_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nip' => 'EMP003',
                'full_name' => 'Budi Santoso',
                'email' => 'budi.santoso@company.com',
                'phone' => '081234567892',
                'date_of_birth' => '1992-12-10',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'nationality' => 'Indonesia',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '12347',
                'country' => 'Indonesia',
                'hire_date' => '2021-06-10',
                'employment_status' => 'active',
                'position' => 'Marketing Specialist',
                'basic_salary' => 7500000,
                'npwp' => '1234567890123458',
                'ptkp_status' => 'TK/2',
                'bpjs_kesehatan_no' => '1234567892',
                'bpjs_tk_no' => '12345678903',
                'department_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nip' => 'EMP004',
                'full_name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@company.com',
                'phone' => '081234567893',
                'date_of_birth' => '1995-03-25',
                'gender' => 'Female',
                'marital_status' => 'Single',
                'nationality' => 'Indonesia',
                'address' => 'Jl. Rasuna Said No. 321, Jakarta',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '12348',
                'country' => 'Indonesia',
                'hire_date' => '2022-02-25',
                'employment_status' => 'active',
                'position' => 'Accountant',
                'basic_salary' => 7000000,
                'npwp' => '1234567890123459',
                'ptkp_status' => 'TK/0',
                'bpjs_kesehatan_no' => '1234567893',
                'bpjs_tk_no' => '12345678904',
                'department_id' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nip' => 'EMP005',
                'full_name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@company.com',
                'phone' => '081234567894',
                'date_of_birth' => '1987-09-18',
                'gender' => 'Male',
                'marital_status' => 'Married',
                'nationality' => 'Indonesia',
                'address' => 'Jl. Sudirman No. 654, Jakarta',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '12349',
                'country' => 'Indonesia',
                'hire_date' => '2018-11-18',
                'employment_status' => 'active',
                'position' => 'Operations Manager',
                'basic_salary' => 12000000,
                'npwp' => '1234567890123460',
                'ptkp_status' => 'TK/3',
                'bpjs_kesehatan_no' => '1234567894',
                'bpjs_tk_no' => '12345678905',
                'department_id' => 5,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Check if employees already exist
        foreach ($employees as $employee) {
            $exists = DB::table('employees')->where('nip', $employee['nip'])->exists();
            if (!$exists) {
                DB::table('employees')->insert($employee);
            }
        }

        $this->command->info('Sample employees seeded successfully!');
    }
}
