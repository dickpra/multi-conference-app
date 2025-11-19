<?php

namespace App\Filament\Chair\Resources;

use App\Enums\SubmissionStatus;
use App\Filament\Chair\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\ConferenceRole;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\ComponentContainer; // <-- Tambahkan import ini
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\ViewEntry;
use Filament\Forms\Components\RichEditor; // <-- Import RichEditor
use Filament\Tables\Columns\ViewColumn;
use App\Mail\PaperAcceptedNotification; // <-- Import
use Illuminate\Support\Facades\Mail; // <-- Import
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\PaperRejectedNotification; // <-- Import
use App\Mail\PaymentRequiredNotification; // Buat mailable ini
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Actions\Action as FormAction; // Alias agar tidak bentrok


class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    // protected static ?string $navigationLabel = 'Makalah Masuk';
    public static function getNavigationLabel(): string
    {
        return __('Makalah Masuk');
    }

    // Beritahu Filament relasi yang menghubungkan Submission ke Conference
    protected static ?string $tenantRelationshipName = 'conference';
    

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Kosongkan, karena Chair tidak mengedit submission
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Judul Makalah'))
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label(__('Nama Penulis'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($state) {
                        switch ($state) {
                            case SubmissionStatus::Submitted:
                                return 'gray';
                            case SubmissionStatus::UnderReview:
                                return 'warning';
                            case SubmissionStatus::Accepted:
                                return 'success';
                            case SubmissionStatus::Rejected:
                                return 'danger';
                            case SubmissionStatus::RevisionRequired:
                                return 'info';
                            case SubmissionStatus::Paid:
                                return 'success';
                            default:
                                return 'secondary';
                        }
                    }),
                    // ->color(fn (SubmissionStatus $state): string => match ($state) {
                    //     SubmissionStatus::Submitted => 'gray',
                    //     SubmissionStatus::UnderReview => 'warning',
                    //     SubmissionStatus::Accepted => 'success',
                    //     SubmissionStatus::Rejected => 'danger',
                    // }),
                // Kolom baru untuk menampilkan reviewer yang sudah ditugaskan
                // --- GANTI 'reviewers.name' MENJADI 'assignedReviewers.name' ---
                ViewColumn::make('reviewers')
                    ->label('Status Reviewer')
                    ->view('infolists.components.reviewer-status-list'),
                // Tables\Columns\TextColumn::make('assignedReviewers.name')
                //     ->label('Reviewers')
                //     ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Tanggal Submit'))
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
                // Tombol Aksi Baru untuk menugaskan reviewer
                Tables\Actions\Action::make('assign')
                ->label('Assign Reviewer')
                ->icon('heroicon-o-user-plus')
                // --- TAMBAHKAN METHOD INI ---
                ->mountUsing(function (ComponentContainer $form, Submission $record) {
                    // Isi form dengan ID reviewer yang sudah ada saat ini
                    $form->fill([
                        'reviewers' => $record->assignedReviewers()->pluck('users.id')->toArray(),
                    ]);
                })
                ->form([
                    CheckboxList::make('reviewers')
                        ->label(__('Pilih Reviewer'))
                        ->options(function () {
                            return Filament::getTenant()->users()
                                ->where('role', ConferenceRole::Reviewer)
                                ->pluck('name', 'users.id');
                        })
                        // Hapus ->required(), karena bisa saja kita ingin mencabut semua reviewer
                        // ->required(), 
                ])
                ->action(function (array $data, Submission $record) {
                    // Method sync() akan otomatis menambah yang baru DAN mencabut yang tidak dicentang
                    $record->assignedReviewers()->sync($data['reviewers']);

                    // Update status hanya jika ada reviewer yang ditugaskan
                    if (count($data['reviewers']) > 0) {
                        $record->update(['status' => SubmissionStatus::UnderReview]);
                    } else {
                        $record->update(['status' => SubmissionStatus::Submitted]);
                    }

                    Notification::make()->title(__('Daftar reviewer berhasil diupdate'))->success()->send();
                }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // --- TAMBAHKAN SECTION INI ---
        \Filament\Infolists\Components\Section::make(__('Informasi Pembayaran'))
            ->schema([
                // 1. Nama Pengirim
                \Filament\Infolists\Components\TextEntry::make('payment_sender_name')
                    ->label(__('Nama Pengirim'))
                    ->icon('heroicon-o-user'),

                // 2. Status Verifikasi
                \Filament\Infolists\Components\TextEntry::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        \App\Enums\SubmissionStatus::PaymentSubmitted => 'warning',
                        \App\Enums\SubmissionStatus::Paid => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        \App\Enums\SubmissionStatus::PaymentSubmitted => __('Perlu Verifikasi'),
                        \App\Enums\SubmissionStatus::Paid => __('Lunas / Terverifikasi'),
                        default => $state instanceof \App\Enums\SubmissionStatus ? $state->getLabel() : $state,
                    }),

                // 3. Tombol Lihat Bukti (Sama seperti Author)
                \Filament\Infolists\Components\TextEntry::make('payment_proof_path')
                    ->label(__('Bukti Transfer'))
                    ->formatStateUsing(fn () => __('Lihat File Bukti'))
                    ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->payment_proof_path))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-eye')
                    ->color('primary'),
            ])
            ->columns(3)
            // Hanya muncul jika sudah ada pembayaran (Submitted atau Paid)
            ->visible(fn ($record) => in_array($record->status, [
                \App\Enums\SubmissionStatus::PaymentSubmitted, 
                \App\Enums\SubmissionStatus::Paid
            ])),
        // --- BATAS AKHIR SECTION BARU ---
            // Bagian ini tetap sama
            Section::make(__('Informasi Makalah'))
                ->headerActions([
                    Action::make('request_revision')
                        ->label(__('Minta Revisi'))
                        ->color('warning')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        // --- TAMBAHKAN FORM INI ---
                        ->form([
                            RichEditor::make('chair_revision_notes')
                                ->label(__('Catatan / Instruksi Revisi untuk Penulis'))
                                ->required(),
                        ])
                        ->action(function (Submission $record, array $data) {
                            $record->update([
                                'status' => SubmissionStatus::RevisionRequired,
                                'chair_revision_notes' => $data['chair_revision_notes'],
                            ]);
                            Notification::make()
                            ->title(__('Permintaan revisi telah dikirim ke penulis'))->success()->send();
                        })
                        ->visible(fn (Submission $record): bool => in_array($record->status, [SubmissionStatus::UnderReview, SubmissionStatus::RevisionSubmitted])),

                    /** TOMBOL ACCEPT BARU **/
                        
                    Action::make('accept')
                    ->label(__('Accept & Tagih Bayaran'))
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar')
                    ->requiresConfirmation()
                    ->modalDescription(__('Paper akan ditandai "Accepted". Author akan menerima email instruksi pembayaran. LoA BELUM akan dikirim.'))
                    ->action(function (Submission $record) {
                        // 1. Update Status ke Accepted (Menunggu Bayar)
                        $record->update(['status' => SubmissionStatus::Accepted]);

                        // 2. Kirim Email Tagihan
                        // Pastikan mailable ini memuat data bank dari $record->conference
                        Mail::to($record->author->email)->send(new PaymentRequiredNotification($record));

                        Notification::make()->title(__('Paper diterima. Notifikasi pembayaran dikirim ke Author.'))->success()->send();
                    })
                    ->visible(fn (Submission $record) => in_array($record->status, [SubmissionStatus::UnderReview, SubmissionStatus::RevisionSubmitted])),
                    // --- TOMBOL ACCEPT LAMA ---
                    // Action::make('accept')
                    //     ->label('Accept')
                    //     ->color('success')
                    //     ->icon('heroicon-o-check-circle')
                    //     // --- GANTI requiresConfirmation() DENGAN INI ---
                    //     ->requiresConfirmation()
                    //     ->modalHeading(__('Accept Makalah dan Kirim LoA'))
                    //     ->modalDescription(__('Apakah Anda yakin ingin menerima makalah ini? Tindakan ini akan mengirimkan Letter of Acceptance (LoA) secara otomatis kepada penulis.'))
                    //     ->modalSubmitActionLabel(__('Ya, Accept dan Kirim'))
                    //     ->action(function (Submission $record) {
                    //         // 1. Buat PDF dari view dan simpan ke storage
                    //         $pdf = PDF::loadView('pdfs.loa', ['submission' => $record]);
                    //         $pdfPath = 'public/loas/loa_' . $record->id . '_' . time() . '.pdf';
                    //         \Illuminate\Support\Facades\Storage::put($pdfPath, $pdf->output());

                    //         // 2. Kirim email ke penulis dengan PDF sebagai lampiran
                    //         Mail::to($record->author->email)->send(new PaperAcceptedNotification($record, $pdfPath));

                    //         // 3. Update status makalah
                    //         $record->update(['status' => SubmissionStatus::Accepted]);

                    //         Notification::make()->title(__('Makalah telah di-Accept dan LoA telah dikirim ke penulis'))->success()->send();
                    //     })
                    //     ->visible(fn (Submission $record): bool => in_array($record->status, [SubmissionStatus::UnderReview, SubmissionStatus::RevisionSubmitted])),


                    Action::make('verify_payment')
                    ->label(__('Verifikasi & Kirim LoA'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Konfirmasi Pembayaran'))
                    ->modalDescription(__('Pastikan data pembayaran valid sebelum menyetujui.'))

                    // --- 1. FORM DI DALAM MODAL ---
                    ->form([
                        // Field Nama Pengirim (Read Only)
                        \Filament\Forms\Components\TextInput::make('payment_sender_name')
                            ->label(__('Nama Pengirim (Sesuai Rekening)'))
                            ->disabled() // Tidak bisa diedit
                            ->dehydrated(false), // Tidak perlu disimpan ulang

                        // Field Gambar Bukti
                        \Filament\Forms\Components\FileUpload::make('payment_proof_path')
                            ->label(__('Bukti Transfer'))
                            ->image()
                            ->imagePreviewHeight('250')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            // Tombol Lihat Full
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('view_full_proof')
                                    ->label(__('Buka Gambar Penuh'))
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->payment_proof_path))
                                    ->openUrlInNewTab()
                            ),
                    ])
                    
                    // --- 2. PENTING: ISI DATA KE FORM ---
                    ->mountUsing(fn ($form, $record) => $form->fill([
                        'payment_sender_name' => $record->payment_sender_name,
                        'payment_proof_path' => $record->payment_proof_path,
                    ]))

                    // --- ACTION ---
                    ->action(function (Submission $record) {
                        // 1. Konten LoA Standar
                        $standardContent = '
                            <p>Kepada Yth. <strong>' . $record->author->name . '</strong>,</p>
                            <p>Dengan hormat,</p>
                            <p>Kami dengan senang hati menginformasikan bahwa makalah Anda yang berjudul:</p>
                            <p style="text-align: center; font-weight: bold; font-style: italic; margin: 20px 0;">"' . $record->title . '"</p>
                            <p>Telah <strong>DITERIMA (ACCEPTED)</strong> untuk dipresentasikan pada <strong>' . $record->conference->name . '</strong>.</p>
                            <p>Kami juga mengonfirmasi bahwa pembayaran biaya registrasi Anda telah kami terima.</p>
                            <p>Terima kasih atas partisipasi Anda, dan sampai jumpa di acara kami.</p>
                        ';
                        
                        // 2. Generate PDF
                        $pdf = Pdf::loadView('pdfs.loa', [
                            'submission' => $record,
                            'content'    => $standardContent 
                        ]);

                        // 3. Tentukan Path Penyimpanan (Relatif terhadap disk 'public')
                        // Contoh hasil: conferences/ai-2025/loas/loa_123.pdf
                        $fileName = 'loa_' . $record->id . '_' . time() . '.pdf';
                        $relativePath = 'conferences/' . $record->conference->slug . '/loas/' . $fileName;
                        
                        // 4. Simpan File ke Disk Public
                        Storage::disk('public')->put($relativePath, $pdf->output());

                        // 5. Kirim Email (Kirim relative path, nanti Mailable yang ubah jadi full path)
                        Mail::to($record->author->email)->send(new PaperAcceptedNotification($record, $relativePath));

                        // 6. Update Database
                        $record->update([
                            'status' => SubmissionStatus::Paid,
                            'loa_path' => $relativePath,
                        ]);

                        Notification::make()->title(__('Pembayaran diverifikasi. LoA telah dikirim ke Author.'))->success()->send();
                    })
                    ->visible(fn (Submission $record) => $record->status === SubmissionStatus::PaymentSubmitted),
                // --- TOMBOL REJECT BARU ---
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(function (Submission $record) {
                        // 1. Kirim email notifikasi penolakan beserta komentar reviewer
                        Mail::to($record->author->email)->send(new PaperRejectedNotification($record));

                        // 2. Update status makalah
                        $record->update(['status' => SubmissionStatus::Rejected]);

                        Notification::make()->title(__('Makalah telah di-Reject dan notifikasi telah dikirim ke penulis'))->danger()->send();
                    })
                    ->visible(fn (Submission $record): bool => in_array($record->status, [SubmissionStatus::UnderReview, SubmissionStatus::RevisionSubmitted])),
                            ])
                ->schema([
                    TextEntry::make('title'),
                    TextEntry::make('author.name'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('keywords')->badge(),
                    TextEntry::make('abstract')->html()->columnSpanFull(),
                    // TAMBAHKAN INI
                    ViewEntry::make('files')
                        ->label(__('Dokumen Terlampir'))
                        ->view('infolists.components.submission-files')
                        ->columnSpanFull(),
                ])->columns(2),

            // --- TAMBAHKAN SECTION BARU INI ---
            Section::make(__('Catatan Revisi dari Anda (Chair)'))
            ->schema([
                TextEntry::make('chair_revision_notes')
                    ->html()
                    ->hiddenLabel(),
            ])
            // Hanya tampilkan section ini jika ada catatan dari chair
            ->visible(fn ($record) => !empty($record->chair_revision_notes))
            ->collapsible(), // Bisa dilipat agar tidak memakan tempat

            // --- TAMBAHKAN SECTION BARU DI BAWAH INI ---
            Section::make(__('Hasil Review'))
                ->schema([
                    RepeatableEntry::make('reviews')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('reviewer.name')
                                ->label(__('Nama Reviewer')),
                            TextEntry::make('recommendation')
                                ->badge(),
                            TextEntry::make('created_at')
                                ->label(__('Tanggal Ulasan'))
                                ->since(),
                            TextEntry::make('comments')
                                ->label(__('Komentar'))
                                ->html()
                                ->columnSpanFull(),
                        ])->columns(3),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'view' => Pages\ViewSubmission::route('/{record}'),
        ];
    }
}