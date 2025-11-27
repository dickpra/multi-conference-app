<?php

namespace App\Filament\Chair\Resources\AttendeeResource\Pages;

use App\Filament\Chair\Resources\AttendeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class ViewAttendee extends ViewRecord
{
    // Pastikan baris ini ada agar tidak error "::class on null"
    protected static string $resource = AttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // COPY LOGIKA VERIFIKASI DARI TABLE KE SINI
            // Agar Chair bisa verifikasi saat melihat detail
            Actions\Action::make('verify_payment')
                ->label(__('Verifikasi Pembayaran'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('Verifikasi Pembayaran Peserta'))
                ->form([
                    Forms\Components\TextInput::make('payment_sender_name')
                        ->label(__('Nama Pengirim'))
                        ->disabled(),
                    Forms\Components\FileUpload::make('payment_proof_path')
                        ->label(__('Bukti Transfer'))
                        ->image()
                        ->imagePreviewHeight('300')
                        ->disabled()
                        ->hintAction(
                            Forms\Components\Actions\Action::make('view_full')
                                ->label(__('Lihat Gambar Penuh'))
                                ->icon('heroicon-m-eye')
                                ->url(fn ($record) => Storage::url($record->payment_proof_path))
                                ->openUrlInNewTab()
                        ),
                ])
                ->mountUsing(fn ($form, $record) => $form->fill([
                    'payment_sender_name' => $record->payment_sender_name,
                    'payment_proof_path' => $record->payment_proof_path,
                ]))
                ->action(function ($record) {
                    $conference = $record->conference;

                    // 1. Update Status
                    $record->update(['status' => 'paid']);
                    $record->refresh();

                    // 2. Regenerate Invoice (LUNAS)
                    $pdfInvoice = Pdf::loadView('pdfs.invoice_attendee', [
                        'attendee' => $record,
                        'conference' => $conference
                    ]);
                    // Gunakan path lama atau buat baru
                    $invoicePath = $record->invoice_path ?: 'conferences/' . $conference->slug . '/invoices/invoice_participant_' . $record->id . '.pdf';
                    Storage::disk('public')->put($invoicePath, $pdfInvoice->output());
                    $record->update(['invoice_path' => $invoicePath]);

                    // 3. Generate Sertifikat
                    $pdfCert = Pdf::loadView('pdfs.certificate', [
                        'attendee' => $record,
                        'conference' => $conference
                    ])->setPaper('a4', 'landscape');
                    
                    $certPath = 'conferences/' . $conference->slug . '/certificates/certificate_' . $record->id . '.pdf';
                    Storage::disk('public')->put($certPath, $pdfCert->output());
                    $record->update(['certificate_path' => $certPath]);

                    Notification::make()->title(__('Peserta Diverifikasi'))->success()->send();
                })
                // Hanya muncul jika status 'payment_submitted'
                ->visible(fn ($record) => $record->status === 'payment_submitted'),
        ];
    }
}