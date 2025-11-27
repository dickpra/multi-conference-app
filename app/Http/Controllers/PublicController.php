<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use Illuminate\Http\Request;
use App\Models\DashboardSetting;
class PublicController extends Controller
{

    public function index(Request $request)
    {
        $now = now();

        // 1. Ambil semua konferensi aktif (tidak dipaginasi)
        $activeConfs = Conference::where('end_date', '>=', $now)
                                ->orderBy('start_date', 'asc')
                                ->get();

        // 2. Siapkan query untuk arsip (konferensi yang sudah lewat)
        $pastConfsQuery = Conference::where('end_date', '<', $now);

        // 3. Terapkan filter jika ada
        if ($request->filled('search')) {
            $search = $request->input('search');
            $pastConfsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('theme', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('year')) {
            $year = $request->input('year');
            $pastConfsQuery->whereYear('start_date', $year);
        }

        // 4. Lakukan pagination pada hasil query arsip
        $pastConfs = $pastConfsQuery->orderBy('start_date', 'desc')->paginate(5); // Tampilkan 5 per halaman

        // 5. Ambil daftar tahun unik untuk dropdown filter
        $years = Conference::where('end_date', '<', $now)
                            ->selectRaw('YEAR(start_date) as year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year');

        $settings = DashboardSetting::firstOrCreate([]);

        // 6. Kirim semua data ke view
        return view('public.index', [
            'activeConfs' => $activeConfs,
            'pastConfs' => $pastConfs,
            'years' => $years,
            'settings' => $settings,
        ]);
    }


    public function show(Conference $conference)
    {
        // Eager load relasi 'schedules' untuk efisiensi query
        $conference->load('schedules');

        // Kirim data conference ke view
        return view('public.conference-show', compact('conference'));
    }

    // app/Http/Controllers/PublicController.php

    public function registerAttendee(Conference $conference)
    {
        // Pastikan user login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Cek apakah sudah terdaftar
        $exists = \App\Models\Attendee::where('user_id', auth()->id())
            ->where('conference_id', $conference->id)
            ->exists();

        if ($exists) {
            return redirect()->route('filament.author.pages.dashboard')
                ->with('error', 'Anda sudah terdaftar sebagai peserta.');
        }

        // Buat data Attendee baru
        \App\Models\Attendee::create([
            'user_id' => auth()->id(),
            'conference_id' => $conference->id,
            'status' => 'pending', // Belum bayar
            // Generate Invoice Number di sini atau nanti
        ]);

        // Tambahkan role di tabel pivot (opsional, tapi bagus untuk konsistensi)
        $conference->users()->attach(auth()->id(), ['role' => \App\Enums\ConferenceRole::Participant]);

        return redirect()->route('filament.author.pages.dashboard')
            ->with('success', 'Berhasil mendaftar sebagai peserta! Silakan lakukan pembayaran.');
    }
}