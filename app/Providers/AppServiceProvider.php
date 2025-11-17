<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

use Filament\Support\Facades\FilamentView;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Filament::renderHook(
        //     'panels::user-menu.before',
        //     fn (): string => view('filament.custom.panel-switcher')->render(),
        // );
        FilamentView::registerRenderHook(
            'panels::global-search.after', // Kaitkan setelah komponen pencarian global
            fn (): string => view('filament.custom.panel-switcher')->render(),
        );
        FilamentView::registerRenderHook(
        'panels::global-search.after', // Posisi: Setelah search bar (sebelahan dengan panel switcher)
        fn (): string => view('filament.custom.language-switcher')->render(),
    );
    }
}
