<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeePanelNavigation
{
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

        // If user is not admin, restrict navigation (only if user is authenticated)
        if ($user && !$user->hasRole('admin')) {
            Filament::serving(function () {
                // Hide all navigation items except dashboard and payroll reports
                Filament::registerRenderHook(
                    'panels::navigation.start',
                    function () {
                        return '
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    // Hide all navigation items
                                    const navItems = document.querySelectorAll("nav a, .fi-nav-item, .fi-nav-group");
                                    navItems.forEach(item => {
                                        const href = item.getAttribute("href") || "";
                                        const text = item.textContent.toLowerCase();

                                        // Only allow dashboard and employee payroll reports
                                        if (!href.includes("/dashboard") &&
                                            !href.includes("/employee-payroll-reports") &&
                                            !text.includes("dashboard") &&
                                            !text.includes("laporan")) {
                                            if (item.closest("li, div, .fi-nav-item")) {
                                                item.closest("li, div, .fi-nav-item").style.display = "none";
                                            }
                                            item.style.display = "none";
                                        }
                                    });

                                    // Hide entire navigation groups that dont contain allowed items
                                    const navGroups = document.querySelectorAll(".fi-nav-group");
                                    navGroups.forEach(group => {
                                        const groupText = group.textContent.toLowerCase();
                                        if (!groupText.includes("dashboard") && !groupText.includes("laporan")) {
                                            group.style.display = "none";
                                        }
                                    });

                                    // Hide user menu items that are not profile or logout
                                    const userMenuItems = document.querySelectorAll(".fi-user-menu a, .fi-dropdown-menu a");
                                    userMenuItems.forEach(item => {
                                        const text = item.textContent.toLowerCase();
                                        if (!text.includes("profile") && !text.includes("logout") && !text.includes("keluar")) {
                                            if (item.closest("li, div")) {
                                                item.closest("li, div").style.display = "none";
                                            }
                                        }
                                    });
                                });
                            </script>
                        ';
                    }
                );
            });
        }

        return $next($request);
    }
}