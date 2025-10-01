<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DepartmentCountWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    protected function getStats(): array
    {
        $totalDepartments = Department::count();
        $totalEmployees = Employee::count();
        $avgEmployeesPerDepartment = $totalDepartments > 0 ? round($totalEmployees / $totalDepartments, 1) : 0;

        return [
            Stat::make('Total Departments', $totalDepartments)
                ->description('Total number of departments')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info')
                ->chart([3, 5, 4, 6, 5, 7, $totalDepartments]),

            Stat::make('Total Employees', $totalEmployees)
                ->description('Total employees across all departments')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Avg Employees/Dept', $avgEmployeesPerDepartment)
                ->description('Average employees per department')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}