<?php

namespace App\Filament\Chair\Resources;

use App\Filament\Chair\Resources\ConferenceHistoryResource\Pages;
use App\Models\Conference; // <-- Menggunakan model Conference yang benar
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConferenceHistoryResource extends Resource
{
    protected static ?string $model = Conference::class; // <-- Menunjuk ke model Conference
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Riwayat Konferensi Saya';
    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        // Ambil ID user yang sedang login
        $userId = auth()->id();

        // Kembalikan HANYA konferensi yang terhubung dengan user ini
        return Conference::query()->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Konferensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('my_role')
                    ->label('Peran Saya')
                    ->state(function (Conference $record): string {
                        // Ambil peran user saat ini dari relasi pivot
                        $user = $record->users()->find(auth()->id());
                        return $user ? str($user->pivot->role)->title() : '-';
                    })
                    ->badge(),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageConferenceHistories::route('/'),
        ];
    }
}