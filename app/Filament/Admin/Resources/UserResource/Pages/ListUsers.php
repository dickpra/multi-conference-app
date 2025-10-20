<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Enums\UserStatus;
use Filament\Resources\Components\Tab;
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    // --- PINDAHKAN LOGIKA TABS KE SINI ---
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua User'),
            'pending' => Tab::make('Menunggu Persetujuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Pending))
                ->badge(User::where('status', UserStatus::Pending)->count()),
            'approved' => Tab::make('Disetujui')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Approved)),
            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', UserStatus::Rejected)),
        ];
    }
}
