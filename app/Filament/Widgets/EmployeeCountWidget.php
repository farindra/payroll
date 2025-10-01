<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeCountWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    protected function getStats(): array
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('employment_status', 'active')->count();
        $inactiveEmployees = Employee::where('employment_status', 'inactive')->count();

        return [
            Stat::make('Total Employees', $totalEmployees)
                ->description('Total number of employees in the system')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, $totalEmployees]),

            Stat::make('Active Employees', $activeEmployees)
                ->description('Currently active employees')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 10, 8, 12, 13, 16, $activeEmployees]),

            Stat::make('Inactive Employees', $inactiveEmployees)
                ->description('Currently inactive employees')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}