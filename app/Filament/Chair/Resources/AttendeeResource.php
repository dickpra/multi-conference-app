<?php

namespace App\Filament\Chair\Resources;

use App\Filament\Chair\Resources\AttendeeResource\Pages;
use App\Models\Attendee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Infolists;


class AttendeeResource extends Resource
{
    protected static ?string $model = Attendee::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Participant (Listener)';
    protected static ?string $pluralModelLabel = 'Participants (List)';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // 1. DATA DIRI PESERTA
            Infolists\Components\Section::make(__('Informasi Peserta'))
                ->icon('heroicon-m-user')
                ->schema([
                    Infolists\Components\TextEntry::make('user.name')
                        ->label(__('Nama Lengkap'))
                        ->weight('bold'),
                    Infolists\Components\TextEntry::make('user.email')
                        ->label(__('Email'))
                        ->icon('heroicon-m-envelope'),
                    Infolists\Components\TextEntry::make('user.country')
                        ->label(__('Negara Asal'))
                        ->icon('heroicon-m-globe-alt'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label(__('Tanggal Mendaftar'))
                        ->date('d F Y H:i'),
                ])->columns(2),

            // 2. STATUS PEMBAYARAN
            Infolists\Components\Section::make(__('Informasi Pembayaran'))
                ->icon('heroicon-m-banknotes')
                ->schema([
                    Infolists\Components\TextEntry::make('status')
                        ->label(__('Status Saat Ini'))
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'payment_submitted' => 'info',
                            'paid' => 'success',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'pending' => __('Belum Bayar'),
                            'payment_submitted' => __('Menunggu Verifikasi'),
                            'paid' => 'Paid',
                            default => $state,
                        }),

                    Infolists\Components\TextEntry::make('payment_sender_name')
                        ->label(__('Nama Pengirim (di Rekening)'))
                        ->visible(fn ($record) => !empty($record->payment_sender_name)),

                    // Tampilkan Gambar Bukti Transfer
                    Infolists\Components\ImageEntry::make('payment_proof_path')
                        ->label(__('Bukti Transfer'))
                        ->height(200)
                        ->columnSpanFull()
                        ->visible(fn ($record) => !empty($record->payment_proof_path)),
                ])->columns(2)
                ->visible(fn ($record) => $record->status !== 'pending'),

            // 3. ARSIP DOKUMEN (Invoice & Sertifikat)
            Infolists\Components\Section::make(__('Dokumen / Output'))
                ->icon('heroicon-m-folder')
                ->collapsible()
                ->schema([
                    // Link Invoice
                    Infolists\Components\TextEntry::make('invoice_path')
                        ->label(__('File Invoice'))
                        ->formatStateUsing(fn ($record) => $record->status === 'paid' ? __('Download Invoice (LUNAS)') : __('Download Tagihan (UNPAID)'))
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->invoice_path) . '?t=' . time())
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-text')
                        ->color('primary'),

                    // Link Sertifikat (Hanya jika Paid)
                    Infolists\Components\TextEntry::make('certificate_path')
                        ->label(__('Sertifikat Kehadiran'))
                        ->formatStateUsing(fn () => __('Download Sertifikat'))
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->certificate_path))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'paid'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Filter: Hanya tampilkan peserta untuk konferensi yang sedang aktif (Tenant)
            // ->query(fn (Builder $query) => $query->where('conference_id', \Filament\Facades\Filament::getTenant()->id))
            // --- GUNAKAN KODE INI ---
            // ->query(function (Builder $query) {
            //     // Cek apakah sistem sedang mendeteksi tenant (Konferensi aktif)
            //     $conference = \Filament\Facades\Filament::getTenant();

            //     // Jika ada (sedang diakses lewat browser oleh Chair)
            //     if ($conference) {
            //         return $query->where('conference_id', $conference->id);
            //     }

            //     // Jika tidak ada (sedang jalan php artisan), kembalikan query standar
            //     return $query;
            // })
            // ------------------------
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Nama Peserta'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('Email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'payment_submitted' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => __('Belum Bayar'),
                        'payment_submitted' => __('Perlu Verifikasi'),
                        'paid' => 'Paid',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Tgl Daftar'))
                    ->date('d M Y'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // ACTION: VERIFIKASI PEMBAYARAN
                Action::make('verify_payment')
                    ->label(__('Verifikasi'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Verifikasi Pembayaran Peserta'))
                    ->modalDescription(__('Aksi ini akan mengubah status menjadi LUNAS, memperbarui Invoice, dan menerbitkan Sertifikat.'))

                    // FORM DI DALAM MODAL
                    ->form([
                        Forms\Components\TextInput::make('payment_sender_name')
                            ->label(__('Nama Pengirim (Sesuai Rekening)'))
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\FileUpload::make('payment_proof_path')
                            ->label(__('Bukti Transfer'))
                            ->image()
                            ->imagePreviewHeight('300')
                            ->disabled()
                            ->dehydrated(false)
                            ->hintAction(
                                Forms\Components\Actions\Action::make('view_full')
                                    ->label(__('Lihat Gambar Penuh'))
                                    ->icon('heroicon-m-eye')
                                    ->url(fn ($record) => Storage::url($record->payment_proof_path))
                                    ->openUrlInNewTab()
                            ),
                    ])
                    // ISI DATA KE FORM MODAL
                    ->mountUsing(fn ($form, $record) => $form->fill([
                        'payment_sender_name' => $record->payment_sender_name,
                        'payment_proof_path' => $record->payment_proof_path,
                    ]))
                    
                    // LOGIKA UTAMA
                    ->action(function (Attendee $record) {
                        $conference = $record->conference;

                        // 1. UPDATE STATUS KE PAID
                        $record->update(['status' => 'paid']);
                        $record->refresh(); // Refresh agar data terbaru masuk ke PDF

                        // 2. REGENERATE INVOICE (JADI LUNAS)
                        $pdfInvoice = Pdf::loadView('pdfs.invoice_attendee', [
                            'attendee' => $record,
                            'conference' => $conference
                        ]);
                        // Timpa file lama atau buat baru jika hilang
                        $invoicePath = $record->invoice_path;
                        if(empty($invoicePath)) {
                             $fileName = 'invoice_participant_' . $record->id . '_' . time() . '.pdf';
                             $invoicePath = 'conferences/' . $conference->slug . '/invoices/' . $fileName;
                             $record->update(['invoice_path' => $invoicePath]);
                        }
                        Storage::disk('public')->put($invoicePath, $pdfInvoice->output());

                        // 3. GENERATE SERTIFIKAT
                        $pdfCert = Pdf::loadView('pdfs.certificate', [
                            'attendee' => $record,
                            'conference' => $conference
                        ])->setPaper('a4', 'landscape'); // Sertifikat biasanya Landscape

                        $certName = 'certificate_' . $record->id . '_' . time() . '.pdf';
                        $certPath = 'conferences/' . $conference->slug . '/certificates/' . $certName;
                        
                        Storage::disk('public')->put($certPath, $pdfCert->output());

                        // 4. SIMPAN PATH SERTIFIKAT
                        $record->update(['certificate_path' => $certPath]);

                        // 5. KIRIM NOTIFIKASI EMAIL (Opsional - Buat Mailable terpisah jika mau)
                        // Mail::to($record->user->email)->send(new AttendeeAcceptedNotification($record));

                        Notification::make()
                            ->title(__('Peserta Diverifikasi'))
                            ->body(__('Invoice lunas dan Sertifikat berhasil diterbitkan.'))
                            ->success()
                            ->send();
                    })
                    // Hanya muncul jika status 'payment_submitted'
                    ->visible(fn (Attendee $record) => $record->status === 'payment_submitted'),
            ]);
    }
    
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendees::route('/'),
            // Kita belum butuh halaman edit/create manual untuk Chair saat ini
            'view' => Pages\ViewAttendee::route('/{record}'),
        ];
    }
}