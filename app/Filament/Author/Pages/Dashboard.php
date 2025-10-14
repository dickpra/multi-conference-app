<?php

namespace App\Filament\Author\Pages;

use App\Models\Conference;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

// Tambahkan "implements HasTable"
class Dashboard extends Page implements HasTable
{
    // Tambahkan "use" ini untuk fungsionalitas tabel
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.author.pages.dashboard';
    protected static ?string $title = 'Pilih Konferensi';
    protected ?string $subheading = 'Pilih salah satu konferensi di bawah ini untuk melihat detail dan mengirimkan makalah.';

    // Method ini akan mendefinisikan tabel kita
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Hanya tampilkan konferensi yang belum berakhir
                Conference::query()
            )
            ->columns([
                TextColumn::make('name')->label('Nama Konferensi')->searchable(),
                TextColumn::make('theme')->label('Tema'),
                TextColumn::make('start_date')->label('Tanggal')->date('d M Y'),
            ])
            ->actions([
                Action::make('select')
                    ->label('Lihat Detail & Submit')
                    ->icon('heroicon-o-arrow-right-circle')
                    // Nanti kita akan buat halaman SubmitPaper ini
                    ->url(fn (Conference $record): string => ConferenceDetail::getUrl(['conference' => $record]))
            ])
            ->defaultSort('start_date', 'asc');
    }
}