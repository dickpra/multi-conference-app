<?php

namespace App\Filament\Chair\Resources\SubmissionResource\Pages;

use App\Enums\SubmissionStatus;
use App\Filament\Chair\Resources\SubmissionResource;
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
            // Tombol "New Submission" tidak kita perlukan di sini
            // Actions\CreateAction::make(),
        ];
    }

    // --- INI ADALAH METHOD KUNCI UNTUK MEMBUAT TABS ---
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Makalah')
                ->badge(static::getResource()::getEloquentQuery()->count()),

            'submitted' => Tab::make('Baru Masuk (Belum Direview)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubmissionStatus::Submitted))
                ->badge(static::getResource()::getEloquentQuery()->where('status', SubmissionStatus::Submitted)->count()),

            'in_review' => Tab::make('Dalam Proses Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::UnderReview,
                    SubmissionStatus::RevisionRequired,
                    SubmissionStatus::RevisionSubmitted
                ]))
                ->badge(static::getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::UnderReview,
                    SubmissionStatus::RevisionRequired,
                    SubmissionStatus::RevisionSubmitted
                ])->count()),

            'finished' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::Accepted,
                    SubmissionStatus::Rejected
                ]))
                ->badge(static::getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::Accepted,
                    SubmissionStatus::Rejected
                ])->count()),
        ];
    }
}