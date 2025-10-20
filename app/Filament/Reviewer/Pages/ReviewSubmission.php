<?php

namespace App\Filament\Reviewer\Pages;

use App\Enums\Recommendation;
use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Review;
use Filament\Infolists\Components\RepeatableEntry;
use App\Enums\SubmissionStatus;
use Filament\Infolists\Components\ViewEntry; // <-- Pastikan ini di-import
use App\Enums\ReviewStatus; 


class ReviewSubmission extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.reviewer.pages.review-submission';

    public Submission $submission;

    public static function getRoutePath(): string
    {
        return '/review-submission/{submission}';
    }

    public function mount(Submission $submission): void
    {
        $this->submission = $submission;
    }

    public function getTitle(): string
    {
        return 'Review: ' . $this->submission->title;
    }

    // Infolist untuk menampilkan detail makalah
    public function submissionInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->submission)
            ->schema([
                Section::make('Detail Makalah')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('title'),
                            TextEntry::make('keywords')->badge(),
                        ]),
                        TextEntry::make('abstract')->html()->columnSpanFull(),
                    ]),
            ]);
    }

    public function reviewHistoryInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->submission)
            ->schema([
                Section::make('Riwayat Ulasan Saya') // Ubah judul agar lebih jelas
                    ->schema([
                        // Ganti RepeatableEntry dengan ViewEntry
                        ViewEntry::make('my_reviews')
                            ->hiddenLabel()
                            ->view('infolists.components.my-review-history'),
                    ])
                    // Tampilkan section ini hanya jika user ini punya review
                    ->visible(fn ($record) => $record->reviews()->where('user_id', auth()->id())->exists()),
            ]);
    }

    // Tombol aksi di pojok kanan atas
    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL UNTUK FILE ASLI
            Action::make('download_original_paper')
                ->label('Unduh File Asli')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => Storage::url($this->submission->full_paper_path))
                ->openUrlInNewTab(),

            // TOMBOL UNTUK FILE REVISI (HANYA MUNCUL JIKA ADA)
            Action::make('download_revised_paper')
                ->label('Unduh File Revisi')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->url(fn () => Storage::url($this->submission->revised_paper_path))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->submission->revised_paper_path !== null),


            // Tombol untuk memberi ulasan
            Action::make('review')
                ->label('Beri Ulasan')
                ->icon('heroicon-o-pencil-square')
                ->form([
                    Radio::make('recommendation')->label('Rekomendasi')->options(Recommendation::class)->required(),
                    RichEditor::make('comments')->label('Komentar')->required(),
                ])
                // --- PERBAIKI BAGIAN INI ---
                // Hapus parameter "Submission $record"
                ->action(function (array $data) {
                    Review::create([
                        // Gunakan $this->submission, bukan $record
                        'submission_id' => $this->submission->id,
                        'user_id' => auth()->id(),
                        'recommendation' => $data['recommendation'],
                        'comments' => $data['comments'],
                    ]);

                    $this->submission->assignedReviewers()->updateExistingPivot(auth()->id(), [
                        'status' => ReviewStatus::Completed,
                    ]);
                    Notification::make()->title('Ulasan berhasil disimpan')->success()->send();
                    return redirect()->route(static::getRouteName(), ['submission' => $this->submission->id]);
                })
                // Tombol hanya tampil jika user BELUM pernah mereview submission ini
                ->hidden(function (): bool {
                    // Tombol disembunyikan jika user ini sudah pernah memberikan ulasan
                    return $this->submission->reviews()->where('user_id', auth()->id())->exists();
                }),
        ];
    }
}