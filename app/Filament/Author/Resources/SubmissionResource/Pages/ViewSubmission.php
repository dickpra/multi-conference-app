<?php

namespace App\Filament\Author\Resources\SubmissionResource\Pages;

use App\Enums\SubmissionStatus;
use App\Filament\Author\Resources\SubmissionResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\ViewEntry;



class ViewSubmission extends ViewRecord implements HasInfolists // <-- Gunakan Trait ini
{
    use InteractsWithInfolists; // <-- Gunakan Trait ini

    protected static string $resource = SubmissionResource::class;

    // Method ini untuk tombol di pojok kanan atas
    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_revision')
                ->label('Unggah Dokumen Revisi')
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray')
                ->mountUsing(function ($form) {
                    $form->fill([
                        'title' => $this->getRecord()->title,
                        'abstract' => $this->getRecord()->abstract,
                    ]);
                })
                ->form([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->label('Judul Makalah (Revisi)')
                        ->required(),
                    \Filament\Forms\Components\RichEditor::make('abstract')
                        ->label('Abstrak (Revisi)')
                        ->required(),
                    \Filament\Forms\Components\FileUpload::make('revised_paper_path')
                        ->label('File Revisi (PDF/DOCX)')
                        ->directory(fn () => 'conferences/' . $this->getRecord()->conference->slug . '/revised-papers')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->getRecord()->update([
                        'title' => $data['title'],
                        'abstract' => $data['abstract'],
                        'revised_paper_path' => $data['revised_paper_path'],
                        'status' => \App\Enums\SubmissionStatus::RevisionSubmitted,
                    ]);
                    \Filament\Notifications\Notification::make()->title('File revisi berhasil diunggah')->success()->send();

                    // --- GANTI BARIS INI ---
                    // $this->refresh(); // Ini Salah
                    return redirect(static::getUrl(['record' => $this->getRecord()])); // Ini Benar
                })
                ->visible($this->getRecord()->status === \App\Enums\SubmissionStatus::RevisionRequired),
        ];
    }

    // Method ini untuk mendefinisikan tampilan detail
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->getRecord())
            ->schema([
                Infolists\Components\Section::make('Detail Makalah')
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('status')->badge(),
                        Infolists\Components\TextEntry::make('keywords')->badge(),
                        Infolists\Components\TextEntry::make('abstract')->html()->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Riwayat File')
                ->schema([
                    ViewEntry::make('files')
                        ->hiddenLabel()
                        ->view('infolists.components.submission-file-history'),
                ]),

                // --- TAMBAHKAN SECTION BARU INI ---
                Infolists\Components\Section::make('Catatan dari Panitia / Chair')
                    ->schema([
                        Infolists\Components\TextEntry::make('chair_revision_notes')
                            ->html()
                            ->hiddenLabel(),
                    ])
                    // Hanya tampilkan section ini jika ada catatan dari chair
                    ->visible(fn ($record) => !empty($record->chair_revision_notes)),
                
                // Infolists\Components\Section::make('Riwayat Ulasan & Revisi')
                //     ->schema([
                //         Infolists\Components\RepeatableEntry::make('reviews')
                //             ->hiddenLabel()
                //             ->schema([
                //                 // PENTING: Kita tidak menampilkan nama reviewer (Double Blind)
                //                 Infolists\Components\TextEntry::make('recommendation')->badge(),
                //                 Infolists\Components\TextEntry::make('created_at')->label('Tanggal Ulasan')->since(),
                //                 Infolists\Components\TextEntry::make('comments')->label('Komentar')->html()->columnSpanFull(),
                //             ])->columns(2),
                //     ])
                //     ->visible(fn ($record) => $record->reviews->isNotEmpty()),
            ]);
    }
}