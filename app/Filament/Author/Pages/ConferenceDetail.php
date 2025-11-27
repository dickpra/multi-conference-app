<?php

namespace App\Filament\Author\Pages;

use App\Models\Conference;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Filament\Author\Pages\SubmitPaper;
use App\Models\Attendee;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Filament\Author\Resources\AttendeeResource;


class ConferenceDetail extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.author.pages.conference-detail';

    public Conference $conference;

    public static function getRoutePath(): string
    {
        return '/conference-detail/{conference}';
    }

    public function mount(Conference $conference): void
    {
        $this->conference = $conference;
    }

    // Definisikan Infolist untuk menampilkan data
    public function conferenceInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->conference)
            ->schema([
                Section::make($this->conference->name)
                    ->description($this->conference->theme)
                    ->schema([
                        Grid::make(2)->schema([
                            ImageEntry::make('logo')->hiddenLabel(),
                            Grid::make(1)->schema([
                                TextEntry::make('location')
                                ->label(__('Lokasi')),
                                TextEntry::make('start_date')
                                    ->label(__('Tanggal'))
                                    ->formatStateUsing(fn ($record) => 
                                        Carbon::parse($record->start_date)->format('d M') . ' - ' . Carbon::parse($record->end_date)->format('d M Y')
                                    ),
                                TextEntry::make('description')
                                    ->label(__('Deskripsi / CFP'))
                                    ->html()
                                    ->columnSpanFull(),
                                TextEntry::make('isbn_issn')
                                    ->label('ISBN/ISSN'),
                                TextEntry::make('paper_template_path')
                                    ->label(__('Unduh Template Paper'))
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('success')
                                    ->url(fn ($record) => Storage::url($record->paper_template_path))
                                    ->openUrlInNewTab()
                                    ->visible(fn ($record): bool => $record->paper_template_path !== null)
                                    ->extraAttributes(['class' => 'cursor-pointer text-primary underline']),
                            ]),
                        ]),
                    ]),
            ]);
    }

    // Tombol Submit Paper

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('submit')
    //             ->label(__('Submit Paper Sekarang'))
    //             ->icon('heroicon-o-document-arrow-up')
    //             ->url(fn (): string => SubmitPaper::getUrl(['conference' => $this->conference]))
    //             // --- TAMBAHKAN KONDISI INI ---
    //             ->visible(fn (): bool => now()->isBefore($this->conference->end_date)),
    //     ];
    // }

    protected function getHeaderActions(): array
    {
        return [
            // 1. TOMBOL SUBMIT PAPER (Presenter)
            Action::make('submit_paper')
                ->label(__('Submit Paper (Presenter)'))
                ->icon('heroicon-o-document-plus')
                ->url(fn (): string => SubmitPaper::getUrl(['conference' => $this->conference]))
                // Sembunyikan jika sudah lewat deadline
                ->visible(fn (): bool => now()->isBefore($this->conference->end_date)),

            // 2. TOMBOL DAFTAR PESERTA (Listener)
            Action::make('register_participant')
                ->label(__('Daftar sebagai Peserta (Listener)'))
                ->icon('heroicon-o-ticket')
                ->color('success') // Warna hijau agar beda
                ->requiresConfirmation()
                ->modalHeading(__('Konfirmasi Pendaftaran'))
                ->modalDescription(__('Anda akan mendaftar sebagai peserta pendengar (Non-Presenter). Invoice tagihan akan dibuat otomatis. Lanjutkan?'))
                ->action(function () {
                    $user = auth()->user();
                    $conference = $this->conference;

                    // A. Cek Duplikasi: Apakah user sudah terdaftar?
                    $existingAttendee = Attendee::where('user_id', $user->id)
                        ->where('conference_id', $conference->id)
                        ->first();

                    if ($existingAttendee) {
                        Notification::make()->title(__('Anda sudah terdaftar sebagai peserta.'))->warning()->send();
                        // Redirect langsung ke halaman pembayaran yang sudah ada
                        return redirect(AttendeeResource::getUrl('index')); 
                    }

                    // B. Buat Data Attendee Baru
                    $attendee = Attendee::create([
                        'user_id' => $user->id,
                        'conference_id' => $conference->id,
                        'status' => 'pending', // Belum bayar
                        'invoice_number' => 'INV-P/' . date('Y') . '/' . $conference->id . '/' . $user->id,
                    ]);

                    // --- TAMBAHAN PENTING: Load relasi user agar tidak null di PDF ---
                    $attendee->load('user'); 
                    // ----------------------------------------------------------------

                    // C. Generate PDF Invoice (Khusus Peserta)
                    // Kita perlu buat view baru: 'pdfs.invoice_attendee'
                    $pdf = Pdf::loadView('pdfs.invoice_attendee', [
                        'attendee' => $attendee,
                        'conference' => $conference
                    ]);

                    $fileName = 'invoice_participant_' . $attendee->id . '_' . time() . '.pdf';
                    $path = 'conferences/' . $conference->slug . '/invoices/' . $fileName;
                    
                    Storage::disk('public')->put($path, $pdf->output());

                    // Update path invoice ke database
                    $attendee->update(['invoice_path' => $path]);

                    Notification::make()->title(__('Pendaftaran berhasil! Silakan lakukan pembayaran.'))->success()->send();

                    // D. Redirect ke Halaman List/Detail Attendee untuk Bayar
                    // Asumsi kita sudah buat AttendeeResource di panel Author
                    return redirect(AttendeeResource::getUrl('index')); 
                })
                // Sembunyikan jika sudah lewat tanggal konferensi
                ->visible(fn (): bool => now()->isBefore($this->conference->end_date)),
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('submit')
    //             ->label('Submit Paper Sekarang')
    //             ->icon('heroicon-o-document-arrow-up')
    //             ->url(fn (): string => SubmitPaper::getUrl(['conference' => $this->conference])),
    //     ];
    // }
}