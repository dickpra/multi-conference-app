<?php

namespace App\Filament\Chair\Pages;

use App\Models\Conference;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;

class SwitchConference extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Ganti Konferensi';
    protected static ?string $navigationGroup = 'Konferensi';
    protected static ?string $title           = 'Pilih / Ganti Konferensi';
    protected static string $view = 'filament.pages.switch-conference';

     protected static ?int $navigationSort = -3;

     
    public function mount(): void
    {
        // Tidak perlu apa-apa; table akan dirender oleh HasTable
    }

    // public function getTableQuery(): Builder
    // {
    //     // Ambil ID user yang sedang login
    //     $userId = auth()->id();

    //     // Kembalikan HANYA konferensi yang terhubung dengan user ini
    //     return Conference::query()->whereHas('users', function ($query) use ($userId) {
    //         $query->where('user_id', $userId);
    //     });
    // }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Conference::query()->whereHas('users', function ($query) {
                    $query->where('user_id', auth()->id());
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('masuk')
                    ->label('Masuk')
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->url(fn (Conference $record) => url("/chair/{$record->slug}"))
                    ->openUrlInNewTab(false),
            ])
            ->defaultSort('start_date', 'desc')
            ->paginated([10, 25, 50])     // pagination
            ->searchDebounce(400);        // search lebih responsif
    }
}
