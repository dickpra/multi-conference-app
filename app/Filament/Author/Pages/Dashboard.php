<?php

namespace App\Filament\Author\Pages;

use App\Filament\Author\Widgets\AuthorStatsOverview;
use App\Models\Conference;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;



class Dashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.author.pages.dashboard';
    // Gunakan method ini agar fungsi translate __() bisa berjalan
    public function getSubheading(): ?string
    {
        return __('Pilih konferensi untuk submit makalah atau lihat status makalah Anda.');
    }

    // Jika $title juga ingin diterjemahkan, gunakan method ini:
    public function getTitle(): string
    {
        return __('Dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Dashboard');
    }

    public static function getModelLabel(): string
    {
        return __('Dashboard');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AuthorStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ambil semua konferensi, urutkan dari yang terbaru
                Conference::query()->orderBy('start_date', 'desc')
            )
            ->heading(__('Pilih Konferensi')) // Judul untuk tabel
            // --- FILTER SEBAGAI PENGGANTI TABS ---
            ->filters([
                SelectFilter::make('status_conference')
                    ->label('Filter Status')
                    ->options([
                        'active' => 'Konferensi Aktif (Bisa Submit)',
                        'finished' => 'Konferensi Selesai (Arsip)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'];

                        // Logika untuk "Aktif"
                        if ($value === 'active') {
                            return $query->where('end_date', '>=', now());
                        }

                        // Logika untuk "Selesai"
                        if ($value === 'finished') {
                            return $query->where('end_date', '<', now());
                        }

                        return $query;
                    })
                    // Opsional: Set default filter ke 'active' agar author fokus ke yang aktif dulu
                    // ->default('active') 
            ])
            // -------------------------------------
            ->columns([
                TextColumn::make('name')->label(__('Nama Konferensi'))->searchable(),

                // --- KOLOM STATUS BARU ---
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->state(function (Conference $record): string {
                        return now()->isAfter($record->end_date) ? __('Selesai') : __('Aktif');
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        __('Selesai') => 'gray',
                        __('Aktif') => 'success',
                    }),

                TextColumn::make('start_date')->label(__('Tanggal Mulai'))->date('d M Y')->sortable(),
            ])
            ->actions([
                Action::make('select')
                    ->label(fn (Conference $record): string => now()->isAfter($record->end_date) ? __('Lihat Arsip') : __('Lihat Detail & Submit'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn (Conference $record): string => ConferenceDetail::getUrl(['conference' => $record])),
            ]);
    }
}