<?php

namespace App\Console\Commands;

use App\Enums\ConferenceRole;
use App\Mail\SubmissionDeadlineReminder;
use App\Models\ConferenceSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Enums\UserStatus;
use App\Models\Submission;

class SendDeadlineReminders extends Command
{
    protected $signature = 'app:send-deadline-reminders';
    protected $description = 'Kirim email pengingat untuk deadline yang akan datang';

    public function handle()
    {
        $this->info('Mulai memeriksa deadline...');
        $reminderDays = [7, 3, 1];

        // 1. Cari semua jadwal yang relevan (misal: "Batas Akhir Revisi")
        // ANDA BISA MENYESUAIKAN JUDUL INI SESUAI KEBUTUHAN PENGINGAT
        $schedules = ConferenceSchedule::where('title', 'like', '%Batas Akhir%')
            ->whereIn(
                'date',
                collect($reminderDays)->map(fn ($day) => now()->addDays($day)->toDateString())
            )
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('Tidak ada deadline yang cocok ditemukan hari ini.');
            return 0;
        }

        foreach ($schedules as $schedule) {
            $conference = $schedule->conference;
            $this->info("Menemukan deadline untuk: {$conference->name}");

            // --- LOGIKA BARU: Ambil user yang SUDAH submit paper di konferensi ini ---
            $recipients = User::query()
                // Gunakan whereHas untuk mencari user yang memiliki submission
                ->whereHas('submissions', function ($query) use ($conference) {
                    // Filter submission hanya untuk konferensi yang relevan
                    $query->where('conference_id', $conference->id);
                })
                ->get();
            // --- AKHIR LOGIKA BARU ---

            if ($recipients->isEmpty()) {
                $this->line("- Tidak ada penulis yang telah submit paper untuk diingatkan.");
                continue;
            }

            foreach ($recipients as $recipient) {
                $this->warn("--> Mencoba mengirim ke: {$recipient->email}");
                Mail::to($recipient->email)->send(new SubmissionDeadlineReminder($schedule));
                $this->line("- Pengingat terkirim.");
            }
        }

        $this->info('Pemeriksaan deadline selesai.');
        return 0;
    }
}