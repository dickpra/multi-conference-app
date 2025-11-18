<?php

namespace App\Filament\Chair\Pages;

use App\Enums\ConferenceRole; // <-- 1. Tambahkan Import ini
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

    // protected static ?string $navigationIcon  = 'heroicon-o-building-library';
    // protected static ?string $navigationLabel = 'Ganti Konferensi';
    // protected static ?string $navigationGroup = 'Konferensi';
    // protected static ?string $title           = 'Pilih Konferensi (Peran Chair)';
    // protected static string $view = 'filament.pages.switch-conference';
    // protected static ?int $navigationSort = -3;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static string $view = 'filament.pages.switch-conference';
    protected static ?int $navigationSort = -3;

    // --- GUNAKAN METHOD DI BAWAH INI ---

    public static function getNavigationLabel(): string
    {
        return __('Ganti Konferensi');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Konferensi');
    }

    // Untuk Judul Halaman (H1), gunakan method ini (biasanya tidak static)
    public function getTitle(): string
    {
        return __('Pilih Konferensi (Peran Chair)');
    }

     
    public function mount(): void
    {
        // ...
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // --- 2. PERBAIKI LOGIKA QUERY INI ---
                Conference::query()->whereHas('users', function (Builder $query) {
                    $query->where('user_id', auth()->id())
                          // Tambahkan filter peran CHAIR di tabel pivot
                          ->where('role', ConferenceRole::Chair);
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nama'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label(__('Lokasi'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('Tanggal'))
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('masuk')
                    ->label(__('Masuk'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    // Cek slug vs ID, asumsikan slug. Jika error 404, ganti $record->id
                    ->url(fn (Conference $record) => url("/chair/{$record->slug}"))
                    ->openUrlInNewTab(false),
            ])
            ->defaultSort('start_date', 'desc')
            ->paginated([10, 25, 50])
            ->searchDebounce(400);
    }
}