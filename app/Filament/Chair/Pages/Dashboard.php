<?php
// app/Filament/Chair/Pages/Dashboard.php

namespace App\Filament\Chair\Pages;

use App\Filament\Chair\Widgets\ConferenceStatsOverview; // <-- Import widget
use Filament\Pages\Dashboard as BasePage;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Facades\Filament;



class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            ConferenceStatsOverview::class, // <-- Daftarkan widget di sini
        ];
    }
    public function getHeading(): string
    {
        $tenant = Filament::getTenant();
        return $tenant ? $tenant->name : 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        $tenant = Filament::getTenant();
        return $tenant ? "Lokasi: {$tenant->location}" : null;
    }
}