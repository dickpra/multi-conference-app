<?php

namespace App\Filament\Chair\Resources\SubmissionResource\Pages;

use App\Enums\SubmissionStatus;
use App\Filament\Chair\Resources\SubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Submission;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament; // <-- 1. Tambahkan import ini


class ListSubmissions extends ListRecords
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_book')
                ->label('Cetak Book of Abstracts')
                ->icon('heroicon-o-book-open')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Sistem akan mengumpulkan semua makalah yang diterima dan menyusunnya menjadi satu file PDF. Proses ini mungkin memakan waktu beberapa saat.')
                // --- 2. GANTI SELURUH BLOK ACTION INI ---
                ->action(function () {
                    // Ambil tenant (konferensi) saat ini menggunakan Facade
                    $conference = Filament::getTenant();

                    $submissions = Submission::where('conference_id', $conference->id)
                        ->where('status', SubmissionStatus::Accepted)
                        ->get();
                    
                    if ($submissions->isEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak ada makalah yang diterima untuk dicetak.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $pdf = PDF::loadView('pdfs.book_of_abstracts', [
                        'conference' => $conference,
                        'submissions' => $submissions
                    ]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'Book_of_Abstracts_' . $conference->slug . '.pdf');
                }),
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