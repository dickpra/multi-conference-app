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



class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Makalah Masuk';

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
                    ->label('Judul Makalah')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Nama Penulis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
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
                    ->label('Tanggal Submit')
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
                        ->label('Pilih Reviewer')
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

                    Notification::make()->title('Daftar reviewer berhasil diupdate')->success()->send();
                }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Bagian ini tetap sama
            Section::make('Informasi Makalah')
                ->headerActions([
                    Action::make('request_revision')
                        ->label('Minta Revisi')
                        ->color('warning')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        // --- TAMBAHKAN FORM INI ---
                        ->form([
                            RichEditor::make('chair_revision_notes')
                                ->label('Catatan / Instruksi Revisi untuk Penulis')
                                ->required(),
                        ])
                        ->action(function (Submission $record, array $data) {
                            $record->update([
                                'status' => SubmissionStatus::RevisionRequired,
                                'chair_revision_notes' => $data['chair_revision_notes'],
                            ]);
                            Notification::make()->title('Permintaan revisi telah dikirim ke penulis')->success()->send();
                        })
                        ->visible(fn (Submission $record): bool => in_array($record->status, [SubmissionStatus::UnderReview, SubmissionStatus::RevisionSubmitted])),

                    // --- TOMBOL ACCEPT BARU ---
                Action::make('accept')
                    ->label('Accept')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    // --- GANTI requiresConfirmation() DENGAN INI ---
                    ->requiresConfirmation()
                    ->modalHeading('Accept Makalah dan Kirim LoA')
                    ->modalDescription('Apakah Anda yakin ingin menerima makalah ini? Tindakan ini akan mengirimkan Letter of Acceptance (LoA) secara otomatis kepada penulis.')
                    ->modalSubmitActionLabel('Ya, Accept dan Kirim')
                    ->action(function (Submission $record) {
                        // 1. Buat PDF dari view dan simpan ke storage
                        $pdf = PDF::loadView('pdfs.loa', ['submission' => $record]);
                        $pdfPath = 'public/loas/loa_' . $record->id . '_' . time() . '.pdf';
                        \Illuminate\Support\Facades\Storage::put($pdfPath, $pdf->output());

                        // 2. Kirim email ke penulis dengan PDF sebagai lampiran
                        Mail::to($record->author->email)->send(new PaperAcceptedNotification($record, $pdfPath));

                        // 3. Update status makalah
                        $record->update(['status' => SubmissionStatus::Accepted]);

                        Notification::make()->title('Makalah telah di-Accept dan LoA telah dikirim ke penulis')->success()->send();
                    })
                    ->visible(fn (Submission $record): bool => in_array($record->status, [SubmissionStatus::UnderReview, SubmissionStatus::RevisionSubmitted])),

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

                        Notification::make()->title('Makalah telah di-Reject dan notifikasi telah dikirim ke penulis')->danger()->send();
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
                        ->label('Dokumen Terlampir')
                        ->view('infolists.components.submission-files')
                        ->columnSpanFull(),
                ])->columns(2),

            // --- TAMBAHKAN SECTION BARU INI ---
            Section::make('Catatan Revisi dari Anda (Chair)')
            ->schema([
                TextEntry::make('chair_revision_notes')
                    ->html()
                    ->hiddenLabel(),
            ])
            // Hanya tampilkan section ini jika ada catatan dari chair
            ->visible(fn ($record) => !empty($record->chair_revision_notes))
            ->collapsible(), // Bisa dilipat agar tidak memakan tempat

            // --- TAMBAHKAN SECTION BARU DI BAWAH INI ---
            Section::make('Hasil Review')
                ->schema([
                    RepeatableEntry::make('reviews')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('reviewer.name')
                                ->label('Nama Reviewer'),
                            TextEntry::make('recommendation')
                                ->badge(),
                            TextEntry::make('created_at')
                                ->label('Tanggal Ulasan')
                                ->since(),
                            TextEntry::make('comments')
                                ->label('Komentar')
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