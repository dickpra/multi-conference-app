<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;


class PaperAcceptedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Submission $submission;
    public string $pdfPath;

    public function __construct(Submission $submission, string $pdfPath)
    {
        $this->submission = $submission;
        $this->pdfPath = $pdfPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! Your Paper Has Been Accepted',
        );
    }

    public function content(): Content
    {
        // --- UBAH BAGIAN INI ---
        return new Content(
            // Ganti 'markdown:' dengan 'view:' dan arahkan ke file baru kita
            view: 'emails.custom_accepted_notification',
        );
    }

    public function attachments(): array
    {
        // Gunakan Storage facade untuk mendapatkan full path yang benar dari disk 'public'
        $fullPath = Storage::disk('public')->path($this->pdfPath);

        return [
            Attachment::fromPath($fullPath)
                ->as('Letter_of_Acceptance.pdf')
                ->withMime('application/pdf'),
        ];
    }
}