<?php

namespace App\Filament\Chair\Widgets;

use App\Models\Submission;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ConferenceSubmissionTrendChart extends ChartWidget
{
    // protected static ?string $heading = 'Aktivitas Submisi (30 Hari Terakhir)';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|null
    {
        return __('Aktivitas Submissi (30 Hari Terakhir)');
    }

    protected function getData(): array
    {
        $conference = Filament::getTenant();

        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $results = Submission::query()
            ->select(DB::raw("DATE(created_at) as date"), DB::raw("COUNT(*) as aggregate"))
            ->where('conference_id', $conference->id)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('aggregate', 'date')
            ->toArray();

        $labels = [];
        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->format('Y-m-d');

            $labels[] = $day->format('d M');
            $data[] = $results[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => (__('Makalah Masuk')),
                    'data' => $data,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.9)',
                    'borderColor' => '#4f46e5',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
