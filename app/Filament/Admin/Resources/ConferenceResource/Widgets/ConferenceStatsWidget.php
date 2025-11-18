<?php
namespace App\Filament\Admin\Resources\ConferenceResource\Widgets;

use App\Enums\SubmissionStatus;
use App\Models\Conference;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ConferenceStatsWidget extends BaseWidget
{
    public ?Conference $record = null;

    protected function getStats(): array
    {
        $totalSubmissions = $this->record->submissions()->count();
        $acceptedSubmissions = $this->record->submissions()->where('status', SubmissionStatus::Accepted)->count();
        $acceptanceRate = $totalSubmissions > 0 ? round(($acceptedSubmissions / $totalSubmissions) * 100, 2) : 0;

        return [
            Stat::make((__('Makalah Masuk')), $totalSubmissions),
            Stat::make('Makalah Diterima', $acceptedSubmissions),
            Stat::make('Tingkat Penerimaan', $acceptanceRate . '%'),
        ];
    }
}