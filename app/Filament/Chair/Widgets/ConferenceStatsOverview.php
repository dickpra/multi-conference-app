<?php

namespace App\Filament\Chair\Widgets;

use App\Enums\ConferenceRole;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConferenceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Ambil data konferensi (tenant) yang sedang aktif
        $conference = Filament::getTenant();

        // Ambil semua user yang terhubung dengan konferensi ini
        $users = $conference->users;

        return [
            Stat::make('Total Tim', $users->count())
                ->description('Total user yang terlibat (Chair, Reviewer, Author)')
                ->icon('heroicon-o-users'),

            Stat::make('Jumlah Reviewer', $users->where('pivot.role', ConferenceRole::Reviewer)->count())
                ->description('User dengan peran sebagai Reviewer')
                ->icon('heroicon-o-academic-cap'),
            
            Stat::make('Jumlah Author', $users->where('pivot.role', ConferenceRole::Author)->count())
                ->description('User dengan peran sebagai Author')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}