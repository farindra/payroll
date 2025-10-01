<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SidebarFooterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add footer to Filament sidebar
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::SIDEBAR_FOOTER,
            function (): \Illuminate\Contracts\View\View {
                return view('components.sidebar-footer');
            }
        );
    }
}
