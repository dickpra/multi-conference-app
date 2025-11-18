<?php

namespace App\Mail;

use App\Models\ConferenceSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionDeadlineReminder extends Mailable
{
    use Queueable, SerializesModels;

    public ConferenceSchedule $schedule;

    public function __construct(ConferenceSchedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Submission Deadline',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.deadline-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}