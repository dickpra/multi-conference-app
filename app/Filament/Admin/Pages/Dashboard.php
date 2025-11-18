<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Admin\Widgets\GlobalStatsOverview;
use App\Filament\Admin\Widgets\UserCountryChart;
use App\Filament\Admin\Widgets\SubmissionTrendChart;


class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            GlobalStatsOverview::class,
            UserCountryChart::class,
            SubmissionTrendChart::class,
        ];
    }
}
