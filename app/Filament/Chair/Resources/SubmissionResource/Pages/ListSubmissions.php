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
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;


class ListSubmissions extends ListRecords
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_book')
                ->label('Generate Book of Abstracts')
                ->icon('heroicon-o-book-open')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription(__('Sistem akan mengumpulkan semua makalah yang diterima dan menyusunnya menjadi satu file PDF. Proses ini mungkin memakan waktu beberapa saat.'))
                // --- 2. GANTI SELURUH BLOK ACTION INI ---
                ->action(function () {
                    // Ambil tenant (konferensi) saat ini menggunakan Facade
                    $conference = Filament::getTenant();

                    $submissions = Submission::where('conference_id', $conference->id)
                        ->where('status', SubmissionStatus::Accepted)
                        ->get();
                    
                    if ($submissions->isEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title(__('Tidak ada makalah yang diterima untuk dicetak.'))
                            ->warning()
                            ->send();
                        return;
                    }

                    $pdf = PDF::loadView('pdfs.book_of_abstracts', [
                        'conference' => $conference,
                        'submissions' => $submissions
                    ]);

                    // --- LOGIKA PENYIMPANAN YANG DIPERBAIKI ---
                    // 1. Path sekarang relatif terhadap root disk 'public' (tanpa 'public/')
                    $filePath = 'conferences/' . $conference->slug . '/books/book_of_abstracts.pdf';
                    
                    // 2. Simpan file secara eksplisit ke disk 'public'
                    Storage::disk('public')->put($filePath, $pdf->output());

                    // 3. Simpan path yang benar ke database
                    $conference->update(['book_of_abstracts_path' => $filePath]);
                    // --- AKHIR PERBAIKAN ---

                    Notification::make()
                        ->title('Book of Abstracts generated successfully')
                        ->success()
                        ->send();

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
            'all' => Tab::make(__('Semua Makalah'))
                ->badge(static::getResource()::getEloquentQuery()->count()),

            'submitted' => Tab::make(__('Baru Masuk (Belum Direview)'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SubmissionStatus::Submitted))
                ->badge(static::getResource()::getEloquentQuery()->where('status', SubmissionStatus::Submitted)->count()),

            'in_review' => Tab::make(__('Dalam Proses Review'))
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
            'payment_pending' => Tab::make(__('Menunggu Pembayaran / Verifikasi'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::Accepted,
                    SubmissionStatus::PaymentSubmitted
                ]))
                ->badge(static::getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::Accepted,
                    SubmissionStatus::PaymentSubmitted
                ])->count()),
            'finished' => Tab::make(__('Selesai'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    SubmissionStatus::Paid,
                    SubmissionStatus::Rejected
                ]))
                ->badge(static::getResource()::getEloquentQuery()->whereIn('status', [
                    SubmissionStatus::Paid,
                    SubmissionStatus::Rejected
                ])->count()),
        ];
    }
}