<?php

namespace App\Filament\Reviewer\Pages;

use App\Enums\ConferenceRole;
use App\Models\Conference;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Filament\Reviewer\Widgets\ReviewerStatsOverview;



class Dashboard extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'heroicon-o-home';
    // protected static string $view = 'filament.reviewer.pages.dashboard';
    // protected static ?string $title = 'Pilih Konferensi';
    // protected ?string $subheading = 'Pilih konferensi untuk melihat tugas review Anda.';

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.reviewer.pages.dashboard';

    // HAPUS properti $title dan $subheading lama, GANTI dengan method di bawah ini:

    public function getTitle(): string
    {
        return __('Pilih Konferensi');
    }

    public function getSubheading(): ?string
    {
        return __('Pilih konferensi untuk melihat tugas review Anda.');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReviewerStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ambil hanya konferensi di mana user ini adalah seorang Reviewer
                auth()->user()->conferences()->where('role', ConferenceRole::Reviewer)->getQuery()
            )
            ->columns([
                TextColumn::make('name')->label(__('Nama Konferensi'))->searchable(),
                TextColumn::make('start_date')->label(__('Tanggal'))->date('d M Y'),
            ])
            ->actions([
                Action::make('view_tasks')
                    ->label(__('Lihat Tugas Review'))
                    ->icon('heroicon-o-arrow-right-circle')
                    // Arahkan ke halaman ConferenceReview yang akan kita buat
                    ->url(fn (Conference $record): string => ConferenceReview::getUrl(['conference' => $record])),
            ])
            ->defaultSort('start_date', 'desc');
    }
}