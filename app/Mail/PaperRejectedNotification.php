<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaperRejectedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Submission $submission;

    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Mengenai Status Makalah Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.paper-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}