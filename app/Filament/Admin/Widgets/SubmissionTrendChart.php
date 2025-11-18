<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Submission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SubmissionTrendChart extends ChartWidget
{
    // protected static ?string $heading = 'Tren Submission Makalah (6 Bulan Terakhir)';
    protected static ?int $sort = 2; // Tampil di urutan kedua
    protected static string $color = 'info';

    public function getHeading(): string|null
    {
        return __('Tren Submission Makalah (6 Bulan Terakhir)');
    }

    protected function getData(): array
    {
        // Rentang 6 bulan: mulai dari 5 bulan lalu (awal bulan) sampai akhir bulan sekarang
        $start = now()->subMonths(5)->startOfMonth();
        $end = now()->endOfMonth();

        // Ambil jumlah per bulan dalam rentang tersebut (format Y-m sebagai key)
        $results = Submission::query()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as aggregate'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('aggregate', 'ym')
            ->toArray();

        // Susun label dan data untuk tiap bulan (pastikan urutan: terlama -> terbaru)
        $labels = [];
        $data = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m'); // sama format dengan query
            $labels[] = $month->format('M Y'); // contoh: "Nov 2025"
            $data[] = isset($results[$key]) ? (int) $results[$key] : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Jumlah Makalah Masuk'),
                    'data' => $data,
                    'fill' => true,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
