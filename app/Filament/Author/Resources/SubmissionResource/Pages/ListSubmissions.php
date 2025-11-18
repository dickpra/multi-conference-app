<?php

namespace App\Filament\Author\Resources\SubmissionResource\Pages;

use App\Enums\SubmissionStatus;
use App\Filament\Author\Resources\SubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSubmissions extends ListRecords
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol "Create" tidak diperlukan karena submit dari halaman lain
        ];
    }

    // --- METHOD KUNCI UNTUK MEMBUAT TABS ---
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('Semua'))
                ->badge(static::getResource()::getEloquentQuery()->count()),

            'in_process' => Tab::make(__('Dalam Proses'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::Submitted,
                    SubmissionStatus::UnderReview,
                    SubmissionStatus::RevisionRequired,
                    SubmissionStatus::RevisionSubmitted
                ]))
                ->badge(static::getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::Submitted,
                    SubmissionStatus::UnderReview,
                    SubmissionStatus::RevisionRequired,
                    SubmissionStatus::RevisionSubmitted
                ])->count()),

            'accepted' => Tab::make(__('Diterima'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubmissionStatus::Accepted))
                ->badge(static::getResource()::getEloquentQuery()->where('status', SubmissionStatus::Accepted)->count()),

            'rejected' => Tab::make(__('Ditolak'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubmissionStatus::Rejected))
                ->badge(static::getResource()::getEloquentQuery()->where('status', SubmissionStatus::Rejected)->count()),
        ];
    }
}