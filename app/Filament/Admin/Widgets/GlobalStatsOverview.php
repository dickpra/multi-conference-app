<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\SubmissionStatus;
use App\Models\Conference;
use App\Models\Submission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; // Tampil paling atas

    protected function getStats(): array
    {
        $totalSubmissions = Submission::count();
        $accepted = Submission::where('status', SubmissionStatus::Accepted)->count();
        $rate = $totalSubmissions > 0 ? round(($accepted / $totalSubmissions) * 100, 1) : 0;

        return [
            // 🔵 STAT 1 — Total Konferensi
            Stat::make(__('Total Konferensi'), Conference::count())
                ->description(__('Jumlah semua konferensi yang terdaftar'))
                ->icon('heroicon-o-building-library')
                ->color('primary')
                ->chart([5, 10, 14, 15, 18, 22, Conference::count()]),

            // 🟡 STAT 2 — Total Makalah Masuk
            Stat::make(__('Total Makalah Masuk'), $totalSubmissions)
                ->description(__("{$accepted} makalah diterima"))
                ->descriptionIcon('heroicon-m-document-check')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->chart([10, 20, 40, 60, 75, 90, $totalSubmissions]),

            // 🔥 STAT 3 — Global Acceptance Rate
            Stat::make(__('Global Acceptance Rate'), $rate . '%')
                ->description(__('Persentase penerimaan seluruh konferensi'))
                ->icon('heroicon-o-chart-pie')
                ->color($rate > 50 ? 'success' : 'danger')
                ->chart([30, 40, 45, 55, 50, 60, $rate]),
        ];
    }
}
