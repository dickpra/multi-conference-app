<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use Illuminate\Http\Request;

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

        // 6. Kirim semua data ke view
        return view('public.index', [
            'activeConfs' => $activeConfs,
            'pastConfs' => $pastConfs,
            'years' => $years,
        ]);
    }


    public function show(Conference $conference)
    {
        // Eager load relasi 'schedules' untuk efisiensi query
        $conference->load('schedules');

        // Kirim data conference ke view
        return view('public.conference-show', compact('conference'));
    }
}