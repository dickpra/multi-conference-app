<?php

namespace App\Filament\Chair\Widgets;

use App\Enums\ConferenceRole;
use App\Models\Submission;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ConferenceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $conference = Filament::getTenant();

        $teamCount = DB::table('conference_user')
            ->where('conference_id', $conference->id)
            ->count();

        $reviewerCount = DB::table('conference_user')
            ->where('conference_id', $conference->id)
            ->where('role', ConferenceRole::Reviewer->value)
            ->count();

        $submissionCount = Submission::where('conference_id', $conference->id)->count();

        return [
            Stat::make(__('Total Tim (Chair & Reviewer)'), $teamCount)
                ->description(__('Jumlah user yang terlibat dalam pengelolaan'))
                ->color('success') // 💚 hijau segar
                ->icon('heroicon-o-users')
                ->chart([3, 5, 7, 6, 10, 12, $teamCount]),

            Stat::make(__('Jumlah Reviewer'), $reviewerCount)
                ->description(__('User dengan peran sebagai Reviewer'))
                ->color('warning') // 🟡 kuning cerah
                ->icon('heroicon-o-academic-cap')
                ->chart([1, 2, 3, 4, 4, 5, $reviewerCount]),

            Stat::make(__('Total Paper Masuk'), $submissionCount)
                ->description(__('Jumlah makalah yang telah disubmit ke konferensi ini'))
                ->color('primary') // 🔵 biru elegan
                ->icon('heroicon-o-document-text')
                ->chart([2, 4, 5, 8, 12, 20, $submissionCount]),
        ];
    }
}
