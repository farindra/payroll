<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'System Administrator',
            'password' => Hash::make('password'),
            'employee_id' => null,
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        // Create employee users
        $employee1 = User::firstOrCreate([
            'email' => 'employee1@example.com',
        ], [
            'name' => 'Employee One',
            'password' => Hash::make('password'),
            'employee_id' => 1,
        ]);

        $employee2 = User::firstOrCreate([
            'email' => 'employee2@example.com',
        ], [
            'name' => 'Employee Two',
            'password' => Hash::make('password'),
            'employee_id' => 2,
        ]);

        // Assign employee roles
        $employee1->assignRole('employee');
        $employee2->assignRole('employee');

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin user: admin@example.com / password');
        $this->command->info('Employee users: employee1@example.com / password, employee2@example.com / password');
    }
}