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
            // Kosong karena submit via dashboard
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge($this->getResource()::getEloquentQuery()->count()),

            'review_process' => Tab::make('Sedang Direview')
                ->icon('heroicon-m-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::Pending,
                    SubmissionStatus::UnderReview,
                    SubmissionStatus::RevisionRequired,
                    SubmissionStatus::RevisionSubmitted,
                ]))
                ->badge($this->getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::Pending,
                    SubmissionStatus::UnderReview,
                    SubmissionStatus::RevisionRequired,
                    SubmissionStatus::RevisionSubmitted,
                ])->count()),

            'payment_process' => Tab::make('Menunggu Pembayaran / Verifikasi')
                ->icon('heroicon-m-banknotes')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::Accepted,         // Sudah di-ACC, belum bayar
                    SubmissionStatus::PaymentSubmitted, // Sudah bayar, tunggu admin
                ]))
                ->badge($this->getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::Accepted,
                    SubmissionStatus::PaymentSubmitted,
                ])->count())
                // Beri warna badge warning agar author sadar ada tagihan
                ->badgeColor('warning'), 

            'completed' => Tab::make('Selesai (LoA)')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubmissionStatus::Paid))
                ->badge($this->getResource()::getEloquentQuery()->where('status', SubmissionStatus::Paid)->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Ditolak')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubmissionStatus::Rejected))
                ->badge($this->getResource()::getEloquentQuery()->where('status', SubmissionStatus::Rejected)->count())
                ->badgeColor('danger'),
        ];
    }
}