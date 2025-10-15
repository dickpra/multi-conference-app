<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

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
            subject: 'Selamat! Makalah Anda Diterima',
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
        return [
            Attachment::fromPath(storage_path('app/' . $this->pdfPath))
                ->as('Letter_of_Acceptance.pdf')
                ->withMime('application/pdf'),
        ];
    }
}