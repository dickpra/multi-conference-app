<?php

namespace App\Filament\Chair\Widgets;

use App\Enums\ConferenceRole;
use App\Models\Submission; // <-- Tambahkan import ini
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ConferenceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $conference = Filament::getTenant();

        // Hitung total user yang terhubung ke konferensi ini (Reviewer + Chair)
        $teamCount = DB::table('conference_user')
            ->where('conference_id', $conference->id)
            ->count();

        // Hitung jumlah Reviewer secara spesifik
        $reviewerCount = DB::table('conference_user')
            ->where('conference_id', $conference->id)
            ->where('role', ConferenceRole::Reviewer->value)
            ->count();
        
        // --- LOGIKA BARU: Hitung total submission untuk konferensi ini ---
        $submissionCount = Submission::where('conference_id', $conference->id)->count();

        return [
            Stat::make(__('Total Tim (Chair & Reviewer)'), $teamCount)
                ->description(__('Jumlah user yang terlibat dalam pengelolaan'))
                ->icon('heroicon-o-users'),

            Stat::make(__('Jumlah Reviewer'), $reviewerCount)
                ->description(__('User dengan peran sebagai Reviewer'))
                ->icon('heroicon-o-academic-cap'),
            
            // --- STATISTIK BARU ---
            Stat::make(__('Total Paper Masuk'), $submissionCount)
                ->description(__('Jumlah makalah yang telah disubmit ke konferensi ini'))
                ->icon('heroicon-o-document-text'),
        ];
    }
}