<?php

namespace App\Http\Controllers;

use App\Enums\ConferenceRole;
use App\Models\Conference;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelSwitchController extends Controller
{
    // Helper untuk mendapatkan user yang sedang login dari guard manapun
    private function getCurrentUser()
    {
        return Auth::guard('chair')->user() 
            ?? Auth::guard('admin')->user() 
            ?? Auth::guard('web')->user();
    }

    public function switchToChair(Conference $conference)
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return redirect()->route('login');
        }

        // Validasi akses Chair
        $isChair = $user->conferences()
            ->where('conference_id', $conference->id)
            ->where('role', ConferenceRole::Chair)
            ->exists();

        if (! $isChair && ! $user->is_super_admin) {
            abort(403, 'Anda tidak memiliki akses Chair untuk konferensi ini.');
        }

        // 1. LOGOUT SEMUA GUARD DULU
        // Ini penting untuk membersihkan sesi lama
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('chair')->logout();

        // 2. LOGIN GUARD TUJUAN
        Auth::guard('chair')->login($user);
        request()->session()->regenerate();

        return redirect()->to(Filament::getPanel('chair')->getUrl($conference));
    }

    public function switchToGeneral()
    {
        $user = $this->getCurrentUser();

        if (!$user) {
             return redirect()->route('login');
        }

        // 1. LOGOUT SEMUA GUARD DULU
        Auth::guard('chair')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout(); // Logout web juga untuk memastikan sesi bersih

        // 2. LOGIN GUARD TUJUAN (WEB)
        Auth::guard('web')->login($user);
        request()->session()->regenerate();

        // 3. Tentukan arah (Reviewer atau Author)
        if ($user->conferences()->where('role', ConferenceRole::Reviewer)->exists()) {
             return redirect()->to(Filament::getPanel('reviewer')->getUrl());
        }

        return redirect()->to(Filament::getPanel('author')->getUrl());
    }
}