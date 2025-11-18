<?php

namespace App\Filament\Chair\Widgets;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ConferenceSubmissionStatusChart extends ChartWidget
{
    // protected static ?string $heading = ('Status Distribusi Makalah');
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Status Distribusi Makalah');
    }

    protected function getData(): array
    {
        // PENTING: Ambil Tenant (Konferensi) yang sedang aktif
        $conference = Filament::getTenant();

        // Ambil data submission khusus konferensi ini
        $data = Submission::where('conference_id', $conference->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('Makalah'),
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#f59e0b', // Warning (Pending/Under Review) - Kuning/Oranye
                        '#10b981', // Success (Accepted) - Hijau
                        '#ef4444', // Danger (Rejected) - Merah
                        '#3b82f6', // Info (Revision) - Biru
                    ],
                ],
            ],
            // Ubah label Enum menjadi teks yang bisa dibaca
            'labels' => array_keys($data), 
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}