<?php

namespace App\Filament\Author\Widgets;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuthorStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $submissions = Submission::where('user_id', auth()->id());

        return [
            Stat::make('Total Makalah Dikirim', $submissions->count())
                ->icon('heroicon-o-document-text'),
            Stat::make('Makalah Diterima', $submissions->clone()->where('status', SubmissionStatus::Accepted)->count())
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Makalah Ditolak', $submissions->clone()->where('status', SubmissionStatus::Rejected)->count())
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}