<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeePanelAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to employee panel and if panel is available
        try {
            $currentPanel = Filament::getCurrentPanel();
            if (!$currentPanel || $currentPanel->getId() !== 'employee') {
                return $next($request);
            }
        } catch (\Exception $e) {
            // If we can't get the current panel, continue normally
            return $next($request);
        }

        $user = Auth::user();

        // Skip middleware for login pages and unauthenticated users
        if (!$user || $request->is('*/login') || $request->is('*/register')) {
            return $next($request);
        }

        // If user is not admin, restrict access
        if (!$user->hasRole('admin')) {
            $currentPath = $request->path();
            $allowedPaths = [
                'employee',
                'employee/dashboard',
                'employee/employee-payroll-reports',
                'employee/profile',
                'employee/logout',
            ];

            // Check if current path is allowed
            $isAllowed = false;
            foreach ($allowedPaths as $allowedPath) {
                if (str_starts_with($currentPath, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }

            // Additional check for resources - block all resources
            if (str_contains($currentPath, '/employees') ||
                str_contains($currentPath, '/departments') ||
                str_contains($currentPath, '/payroll-periods') ||
                str_contains($currentPath, '/salary-components') ||
                str_contains($currentPath, '/users') ||
                str_contains($currentPath, '/permissions') ||
                str_contains($currentPath, '/shield') ||
                str_contains($currentPath, '/import-attendance') ||
                str_contains($currentPath, '/payroll-reports') ||
                str_contains($currentPath, '/process-payroll')) {
                $isAllowed = false;
            }

            if (!$isAllowed) {
                // Redirect to dashboard with error message
                return redirect()->to('/employee')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
        }

        return $next($request);
    }
}