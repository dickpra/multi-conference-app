<?php

namespace App\Http\Controllers;

use App\Models\Conference;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function show(Conference $conference)
    {
        // Eager load relasi 'schedules' untuk efisiensi query
        $conference->load('schedules');

        // Kirim data conference ke view
        return view('public.conference-show', compact('conference'));
    }
}