<?php
// app/Filament/Chair/Pages/Dashboard.php

namespace App\Filament\Chair\Pages;

use App\Filament\Chair\Widgets\ConferenceStatsOverview; // <-- Import widget
use App\Filament\Chair\Widgets\ConferenceSubmissionStatusChart;
use App\Filament\Chair\Widgets\ConferenceSubmissionTrendChart;
use App\Models\Conference;
use Filament\Pages\Dashboard as BasePage;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Facades\Filament;



class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            ConferenceStatsOverview::class, // <-- Daftarkan widget di sini
            ConferenceSubmissionStatusChart::class,
            ConferenceSubmissionTrendChart::class,
        ];
    }
    public function getHeading(): string
    {
        $tenant = Filament::getTenant();
        return $tenant ? $tenant->name : (__('Dashboard'));
    }

    public function getSubheading(): ?string
    {
        $tenant = Filament::getTenant();
        return $tenant ? __("Lokasi: {$tenant->location}") : null;
    }
}