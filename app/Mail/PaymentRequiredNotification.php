<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRequiredNotification extends Mailable
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
            subject: 'Payment Required: ' . $this->submission->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            // Kita gunakan view HTML biasa agar lebih fleksibel daripada markdown
            view: 'emails.payment-required',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}