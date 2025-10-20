<?php

namespace App\Filament\Reviewer\Widgets;

use App\Models\Submission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ReviewerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Base query for all submissions assigned to this reviewer
        $assignedSubmissions = Submission::whereHas('assignedReviewers', function (Builder $query) {
            $query->where('user_id', auth()->id());
        });

        // Query for submissions that still need a review
        $needsReviewQuery = $assignedSubmissions->clone()->where(function (Builder $subQuery) {
            $subQuery->whereDoesntHave('reviews', fn(Builder $q) => $q->where('user_id', auth()->id()))
                ->orWhere(function (Builder $q2) {
                    $q2->whereHas('reviews', fn(Builder $q3) => $q3->where('user_id', auth()->id()))
                       ->whereRaw('submissions.updated_at > (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [auth()->id()]);
                });
        });

        $totalTasks = $assignedSubmissions->count();
        $pendingTasks = $needsReviewQuery->count();
        $completedTasks = $totalTasks - $pendingTasks;

        return [
            Stat::make('Total Tugas Review', $totalTasks)
                ->icon('heroicon-o-document-duplicate'),
            Stat::make('Membutuhkan Ulasan', $pendingTasks)
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('Selesai Diulas', $completedTasks)
                ->color('success')
                ->icon('heroicon-o-check-badge'),
        ];
    }
}