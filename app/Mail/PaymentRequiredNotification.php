<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment; // Import ini
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage; // Import ini

class PaymentRequiredNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Submission $submission;
    public string $invoicePath; // Properti baru

    public function __construct(Submission $submission, string $invoicePath)
    {
        $this->submission = $submission;
        $this->invoicePath = $invoicePath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paper Accepted & Payment Invoice: ' . $this->submission->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-required',
        );
    }

    // Tambahkan method attachments
    public function attachments(): array
    {
        // Ambil full path dari storage public
        $fullPath = Storage::disk('public')->path($this->invoicePath);

        return [
            Attachment::fromPath($fullPath)
                ->as('Invoice_' . $this->submission->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}