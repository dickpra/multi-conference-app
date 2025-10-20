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

class Dashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.author.pages.dashboard';
    protected static ?string $title = 'Dashboard';
    protected ?string $subheading = 'Pilih konferensi untuk submit makalah atau lihat status makalah Anda.';

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
            ->heading('Pilih Konferensi') // Judul untuk tabel
            ->columns([
                TextColumn::make('name')->label('Nama Konferensi')->searchable(),
                
                // --- KOLOM STATUS BARU ---
                TextColumn::make('status')
                    ->label('Status')
                    ->state(function (Conference $record): string {
                        return now()->isAfter($record->end_date) ? 'Selesai' : 'Aktif';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Selesai' => 'gray',
                        'Aktif' => 'success',
                    }),

                TextColumn::make('start_date')->label('Tanggal Mulai')->date('d M Y')->sortable(),
            ])
            ->actions([
                Action::make('select')
                    ->label(fn (Conference $record): string => now()->isAfter($record->end_date) ? 'Lihat Arsip' : 'Lihat Detail & Submit')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn (Conference $record): string => ConferenceDetail::getUrl(['conference' => $record])),
            ]);
    }
}