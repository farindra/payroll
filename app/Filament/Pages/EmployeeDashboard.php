<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string $view = 'filament.pages.employee-dashboard';
    protected static ?string $title = 'Dashboard Employee';

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->employee !== null;
    }

    public function mount()
    {
        // Ensure user is authenticated and has employee relationship
        if (!Auth::check() || !Auth::user()->employee) {
            abort(403, 'Anda tidak memiliki akses ke dashboard employee');
        }
    }

    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getSubheading(): string
    {
        return 'Selamat datang di Portal Employee';
    }
}