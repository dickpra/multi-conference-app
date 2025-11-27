<?php

namespace App\Filament\Author\Resources;

use App\Filament\Author\Resources\AttendeeResource\Pages;
use App\Models\Attendee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists; // <-- Import Infolists
use Filament\Infolists\Infolist; // <-- Import Infolist Class
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendeeResource extends Resource
{
    protected static ?string $model = Attendee::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Participants';
    protected static ?string $pluralModelLabel = 'Attendances List';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool { return false; }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc'); // atau ->latest()
    }

    // --- DEFINISI INFOLIST (TAMPILAN DETAIL) ---
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // 1. DETAIL KONFERENSI
            Infolists\Components\Section::make(__('Detail Acara'))
                ->icon('heroicon-m-calendar-days')
                ->schema([
                    Infolists\Components\TextEntry::make('conference.name')
                        ->label(__('Nama Konferensi'))
                        ->weight('bold')
                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                    
                    Infolists\Components\TextEntry::make('conference.start_date')
                        ->label(__('Tanggal Pelaksanaan'))
                        ->date('d F Y')
                        ->icon('heroicon-m-calendar'),
                        
                    Infolists\Components\TextEntry::make('conference.location')
                        ->label(__('Lokasi'))
                        ->icon('heroicon-m-map-pin'),
                ])->columns(3),

            // 2. INSTRUKSI PEMBAYARAN (Hanya muncul jika BELUM LUNAS)
            // --- UPDATE SECTION INI ---
            \Filament\Infolists\Components\Section::make(__('Instruksi Pembayaran (Internasional & Lokal)'))
                ->icon('heroicon-m-banknotes')
                // ->color('danger')
                ->schema([
                    // 1. Total Tagihan (Gunakan participant_fee)
                    \Filament\Infolists\Components\TextEntry::make('conference.participant_fee')
                        ->label(__('TOTAL TAGIHAN PESERTA'))
                        ->money('IDR')
                        ->weight('bold')
                        ->size(\Filament\Infolists\Components\TextEntry\TextEntrySize::Large)
                        ->color('danger')
                        ->columnSpanFull(),

                    // 2. Info Organisasi
                    \Filament\Infolists\Components\TextEntry::make('conference.vat_number')
                        ->label('VAT Number')
                        ->visible(fn ($record) => !empty($record->conference->vat_number)),
                    
                    \Filament\Infolists\Components\TextEntry::make('conference.postal_address')
                        ->label('Organization Address')
                        ->columnSpanFull()
                        ->visible(fn ($record) => !empty($record->conference->postal_address)),

                    // 3. Detail Bank Lengkap
                    \Filament\Infolists\Components\Group::make([
                        \Filament\Infolists\Components\TextEntry::make('conference.bank_name')->label('Bank Name'),
                        \Filament\Infolists\Components\TextEntry::make('conference.bank_account_number')
                            ->label('Account Number')
                            ->weight('bold')
                            ->copyable(),
                        \Filament\Infolists\Components\TextEntry::make('conference.swift_code')
                            ->label('SWIFT Code')
                            ->visible(fn ($record) => !empty($record->conference->swift_code)),
                    ])->columnSpan(1),

                    \Filament\Infolists\Components\Group::make([
                        \Filament\Infolists\Components\TextEntry::make('conference.bank_account_holder')->label('Account Holder'),
                        \Filament\Infolists\Components\TextEntry::make('conference.bank_city')->label('Bank City'),
                        \Filament\Infolists\Components\TextEntry::make('conference.bank_account_address')
                            ->label('Bank Address')
                            ->visible(fn ($record) => !empty($record->conference->bank_account_address)),
                    ])->columnSpan(1),
                ])
                ->columns(2)
                // Hanya muncul jika status masih pending
                ->visible(fn ($record) => $record->status === 'pending'),

            // 3. STATUS & BUKTI PEMBAYARAN (Jika sudah upload)
            Infolists\Components\Section::make(__('Status Pembayaran'))
                ->icon('heroicon-m-credit-card')
                ->schema([
                    Infolists\Components\TextEntry::make('payment_sender_name')
                        ->label(__('Nama Pengirim'))
                        ->icon('heroicon-m-user'),

                    Infolists\Components\TextEntry::make('status')
                        ->label(__('Status Verifikasi'))
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'payment_submitted' => 'warning',
                            'paid' => 'success',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'payment_submitted' => __('Menunggu Verifikasi Admin'),
                            'paid' => __('Lunas / Terverifikasi'),
                            default => ucfirst($state),
                        }),

                    Infolists\Components\TextEntry::make('payment_proof_path')
                        ->label('Bukti Transfer')
                        ->formatStateUsing(fn () => __('Lihat File'))
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->payment_proof_path))
                        ->openUrlInNewTab()
                        ->icon('heroicon-m-eye')
                        ->color('primary'),
                ])
                ->columns(3)
                ->visible(fn (Attendee $record) => in_array($record->status, ['payment_submitted', 'paid'])),

            // 4. DOKUMEN (Invoice & Sertifikat)
            Infolists\Components\Section::make('Dokumen Saya')
                ->icon('heroicon-m-folder')
                ->schema([
                    // Invoice (Selalu ada)
                    Infolists\Components\TextEntry::make('invoice_path')
                        ->label('Invoice / Kuitansi')
                        ->formatStateUsing(fn ($record) => $record->status === 'paid' ? __('Download Kuitansi Lunas') : __('Download Tagihan'))
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->invoice_path) . '?t=' . time())
                        ->openUrlInNewTab()
                        ->icon('heroicon-m-document-currency-dollar')
                        ->color(fn ($record) => $record->status === 'paid' ? 'success' : 'warning'),

                    // Sertifikat (Hanya jika Paid & Ada File)
                    Infolists\Components\TextEntry::make('certificate_path')
                        ->label(__('Sertifikat Kehadiran'))
                        ->formatStateUsing(fn () => __('Download Sertifikat'))
                        ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->certificate_path))
                        ->openUrlInNewTab()
                        ->icon('heroicon-m-academic-cap')
                        ->color('primary')
                        ->visible(fn ($record) => $record->status === 'paid' && !empty($record->certificate_path)),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('conference.name')
                    ->label(__('Konferensi'))
                    ->searchable()
                    ->sortable()
                    ->wrap(), // Agar teks panjang turun ke bawah

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
                        'payment_submitted' => __('Menunggu Verifikasi Admin'),
                        'paid' => 'Paid',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Tgl Daftar'))
                    ->date('d M Y'),
            ])
            ->actions([
                // Tombol View (Mata) -> Mengarah ke Halaman Detail Baru
                Tables\Actions\ViewAction::make(), 

                // Tombol Upload (Jika belum lunas)
                Tables\Actions\Action::make('upload_payment')
                    ->label(__('Bayar'))
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->button() // Tampil sebagai tombol kecil
                    ->visible(fn (Attendee $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\TextInput::make('payment_sender_name')
                            ->label(__('Nama Pengirim'))
                            ->required(),
                        Forms\Components\FileUpload::make('payment_proof_path')
                            ->label(__('Bukti Transfer'))
                            ->directory('attendee-payments')
                            ->required(),
                    ])
                    ->action(function (Attendee $record, array $data) {
                        $record->update([
                            'payment_sender_name' => $data['payment_sender_name'],
                            'payment_proof_path' => $data['payment_proof_path'],
                            'status' => 'payment_submitted',
                        ]);
                        \Filament\Notifications\Notification::make()->title(__('Bukti terkirim. Tunggu verifikasi admin.'))->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendees::route('/'),
            // Daftarkan rute view
            'view' => Pages\ViewAttendee::route('/{record}'),
        ];
    }
}