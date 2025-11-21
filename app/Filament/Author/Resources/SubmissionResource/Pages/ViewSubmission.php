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
use Filament\Infolists\Components\TextEntry;


class ViewSubmission extends ViewRecord implements HasInfolists // <-- Gunakan Trait ini
{
    use InteractsWithInfolists; // <-- Gunakan Trait ini

    protected static string $resource = SubmissionResource::class;

    // Method ini untuk tombol di pojok kanan atas
    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_payment')
                ->label(__('Unggah Bukti Pembayaran'))
                ->icon('heroicon-o-credit-card')
                ->form([
                    // --- INPUT BARU ---
                    \Filament\Forms\Components\TextInput::make('payment_sender_name')
                        ->label(__('Nama Pengirim (Sesuai Rekening)'))
                        ->required()
                        ->placeholder(__('Contoh: Ahmad Dahlan')),

                    \Filament\Forms\Components\FileUpload::make('payment_proof_path')
                        ->label(__('Foto Bukti Transfer'))
                        ->image()
                        ->directory('payment-proofs')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->getRecord()->update([
                        'payment_sender_name' => $data['payment_sender_name'], // Simpan nama
                        'payment_proof_path' => $data['payment_proof_path'],
                        'status' => \App\Enums\SubmissionStatus::PaymentSubmitted,
                    ]);

                    Notification::make()->title(__('Bukti pembayaran berhasil diunggah.'))->success()->send();
                    return redirect(static::getUrl(['record' => $this->getRecord()]));
                })
                ->visible(fn () => $this->getRecord()->status === \App\Enums\SubmissionStatus::Accepted),
            Action::make('upload_revision')
                ->label(__('Unggah Dokumen Revisi'))
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
                        ->label(__('Judul Makalah (Revisi)'))
                        ->required(),
                    \Filament\Forms\Components\RichEditor::make('abstract')
                        ->label(__('Abstrak (Revisi)'))
                        ->required(),
                    \Filament\Forms\Components\FileUpload::make('revised_paper_path')
                        ->label(__('File Revisi (PDF/DOCX)'))
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
                    \Filament\Notifications\Notification::make()->title(__('File revisi berhasil diunggah'))->success()->send();

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
                \Filament\Infolists\Components\Section::make(__('Dokumen Administrasi'))
                ->schema([
                    // 1. Tombol Download Invoice (Muncul di kedua kondisi)
                    \Filament\Infolists\Components\TextEntry::make('invoice_path')
                        ->label(__('Invoice / Bukti Pembayaran'))
                        ->formatStateUsing(fn ($record) => 
                            $record->status === \App\Enums\SubmissionStatus::Paid 
                            ? __('Download Kuitansi Lunas (PAID)') 
                            : __('Download Tagihan (UNPAID)')
                        )
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->invoice_path))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-currency-dollar')
                        ->color(fn ($record) => 
                            $record->status === \App\Enums\SubmissionStatus::Paid ? 'success' : 'warning'
                        ),

                    // 2. Tombol Download LoA (Hanya muncul jika Paid)
                    \Filament\Infolists\Components\TextEntry::make('loa_path')
                        ->label('Letter of Acceptance (LoA)')
                        ->formatStateUsing(fn () => 'Download Dokumen LoA')
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->loa_path))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-check')
                        ->color('primary')
                        ->visible(fn ($record) => $record->status === \App\Enums\SubmissionStatus::Paid),
                ])
                ->columns(2)
                // Section ini muncul jika sudah tahap pembayaran atau selesai
                ->visible(fn ($record) => in_array($record->status, [
                    \App\Enums\SubmissionStatus::Accepted,
                    \App\Enums\SubmissionStatus::PaymentSubmitted,
                    \App\Enums\SubmissionStatus::Paid
                ])),
                \Filament\Infolists\Components\Section::make(__(('Instruksi Pembayaran')))
                    ->schema([
                            // --- TAMBAHAN: Link Download Invoice ---
                \Filament\Infolists\Components\TextEntry::make('invoice_number')
                    ->label(__('Nomor Invoice'))
                    ->weight('bold')
                    ->copyable(),

                \Filament\Infolists\Components\TextEntry::make('invoice_path')
                    ->label(__('Dokumen Tagihan'))
                    ->formatStateUsing(fn () => __('Download Invoice Resmi (PDF)'))
                    ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->invoice_path))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->columnSpanFull(), // Biar lebar penuh di bawah info bank
                    // Baris 1: Nominal & VAT
                    \Filament\Infolists\Components\TextEntry::make('conference.registration_fee')
                        ->label(__('TOTAL TAGIHAN'))
                        ->money('IDR')
                        ->weight('bold')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->color('danger'),
                    
                    \Filament\Infolists\Components\TextEntry::make('conference.vat_number')
                        ->label(__('VAT Number'))
                        ->visible(fn ($record) => !empty($record->conference->vat_number)),

                    // Baris 2: Detail Bank Utama
                    \Filament\Infolists\Components\TextEntry::make('conference.bank_name')
                        ->label('Bank Name'),
                    \Filament\Infolists\Components\TextEntry::make('conference.bank_account_number')
                        ->label('Account Number')
                        ->copyable()
                        ->weight('bold'),
                    \Filament\Infolists\Components\TextEntry::make('conference.swift_code')
                        ->label('SWIFT / BIC Code')
                        ->visible(fn ($record) => !empty($record->conference->swift_code)),

                    // Baris 3: Detail Pemilik
                    \Filament\Infolists\Components\TextEntry::make('conference.bank_account_holder')
                        ->label('Account Holder Name'),
                    \Filament\Infolists\Components\TextEntry::make('conference.bank_city')
                        ->label('City'),
                    
                    // Baris 4: Alamat Lengkap (Full Width)
                    \Filament\Infolists\Components\TextEntry::make('conference.bank_account_address')
                        ->label('Account Holder Address')
                        ->columnSpanFull()
                        ->visible(fn ($record) => !empty($record->conference->bank_account_address)),
                        
                    \Filament\Infolists\Components\TextEntry::make('conference.postal_address')
                        ->label('Organization Address')
                        ->columnSpanFull()
                        ->visible(fn ($record) => !empty($record->conference->postal_address)),
                        
                ])
                ->columns(3)
                ->visible(fn ($record) => $record->status === \App\Enums\SubmissionStatus::Accepted),
                    // SECTION STATUS PEMBAYARAN (Jika sudah upload)
                \Filament\Infolists\Components\Section::make(__('Status Pembayaran'))
                    ->schema([
                        // 1. Tampilkan Nama Pengirim
                        \Filament\Infolists\Components\TextEntry::make('payment_sender_name')
                            ->label(__('Nama Pengirim (Di Rekening)'))
                            ->icon('heroicon-o-user'),

                        // 2. Tampilkan Status
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->label(__('Status Verifikasi'))
                            ->badge()
                            // --- PERBAIKAN 1: Hapus 'string' dan gunakan Enum Case untuk Color ---
                            ->color(fn ($state): string => match ($state) {
                                \App\Enums\SubmissionStatus::PaymentSubmitted => 'warning',
                                \App\Enums\SubmissionStatus::Paid => 'success',
                                default => 'gray',
                            })
                            // --- PERBAIKAN 2: Hapus 'string' dan gunakan Enum Case untuk Format Text ---
                            ->formatStateUsing(fn ($state) => match ($state) {
                                \App\Enums\SubmissionStatus::PaymentSubmitted => __('Menunggu Verifikasi Admin'),
                                \App\Enums\SubmissionStatus::Paid => __('Pembayaran Diterima'),
                                // Tampilkan label default enum jika status lain
                                default => $state instanceof \App\Enums\SubmissionStatus ? $state->getLabel() : $state,
                            }),

                        // 3. Tombol Lihat Bukti Transfer
                        \Filament\Infolists\Components\TextEntry::make('payment_proof_path')
                            ->label(__('Bukti Transfer'))
                            ->formatStateUsing(fn () => __('Lihat File'))
                            ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->payment_proof_path))
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-eye')
                            ->color('primary'),
                    ])
                    ->columns(3) // Agar rapi berjajar
                    ->visible(fn ($record) => in_array($record->status, [
                        \App\Enums\SubmissionStatus::PaymentSubmitted, 
                        \App\Enums\SubmissionStatus::Paid
                    ])),
                Infolists\Components\Section::make(__('Detail Makalah'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('status')->badge(),
                        Infolists\Components\TextEntry::make('keywords')->badge(),
                        Infolists\Components\TextEntry::make('abstract')->html()->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make(__('Riwayat File'))
                ->schema([
                    ViewEntry::make('files')
                        ->hiddenLabel()
                        ->view('infolists.components.submission-file-history'),
                ]),

                // --- TAMBAHKAN SECTION BARU INI ---
                Infolists\Components\Section::make(__('Catatan dari Panitia / Chair'))
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