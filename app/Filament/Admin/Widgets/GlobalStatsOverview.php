<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Conference;
use App\Models\Submission;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Konferensi', Conference::count())
                ->icon('heroicon-o-building-library'),
            Stat::make('Total Pengguna', User::count())
                ->icon('heroicon-o-users'),
            Stat::make('Total Makalah Masuk', Submission::count())
                ->icon('heroicon-o-document-text'),
        ];
    }
}