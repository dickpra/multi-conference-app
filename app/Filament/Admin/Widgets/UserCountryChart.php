<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserCountryChart extends ChartWidget
{
    // protected static ?string $heading = 'Top 5 Negara Asal Pengguna';
    protected static ?int $sort = 3; // Tampil di urutan ketiga
    protected static ?string $maxHeight = '300px'; // Agar tidak terlalu besar

    public function getHeading(): string|null
    {
        return __('Top 5 Negara Asal Pengguna');
    }

    
    protected function getData(): array
    {
        // Query untuk menghitung user per negara
        $data = User::query()
            ->select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'User',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // Biru
                        '#ef4444', // Merah
                        '#10b981', // Hijau
                        '#f59e0b', // Kuning
                        '#8b5cf6', // Ungu
                    ],
                ],
            ],
            'labels' => $data->pluck('country')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Tipe grafik donat
    }
}