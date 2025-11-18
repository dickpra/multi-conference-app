<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\ConferenceRole;
use App\Enums\UserStatus;

class LoginController extends Controller
{
    /**
     * Menampilkan form login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses upaya login.
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        // --- LOGIKA PENGECEKAN STATUS BARU ---
        // --- LOGIKA PENGECEKAN STATUS YANG DIPERBAIKI ---
        // Gunakan ->value untuk perbandingan yang akurat
        if ($user->status !== UserStatus::Approved->value) {
            // Tentukan pesan error berdasarkan status
            $message = 'This account is not allowed to log in.';
            if ($user->status === UserStatus::Pending->value) {
                $message = 'Your account is pending approval from an admin.';
            } elseif ($user->status === UserStatus::Rejected->value) {
                $message = 'Your account has been rejected by an admin.';
            }
            throw ValidationException::withMessages(['email' => $message]);
        }
        // --- AKHIR LOGIKA BARU ---

        // if ($user->is_super_admin) {
        //     $request->session()->regenerate();
        //     Auth::guard('admin')->login($user, $request->boolean('remember'));
        //     return redirect()->intended(Filament::getPanel('admin')->getUrl());
        // }

        // // --- LOGIKA BARU UNTUK CHAIR ---
        // $conferences = $user->conferences; // Ambil semua konferensi yang bisa diakses user

        // // if ($conferences->isNotEmpty()) { // Cek apakah user punya akses ke setidaknya satu konferensi
        // //     $request->session()->regenerate();
        // //     Auth::guard('chair')->login($user, $request->boolean('remember'));

        // //     // Ambil konferensi pertama sebagai tujuan redirect
        // //     $firstConference = $conferences->first();

        // //     // Arahkan ke URL dashboard dari konferensi tersebut, yang sudah lengkap dengan slug
        // //     return redirect()->intended(
        // //         Filament::getPanel('chair')->getUrl($firstConference)
        // //     );
        // // }

        // if ($user->conferences()->where('role', ConferenceRole::Chair)->exists()) {
        //     $request->session()->regenerate();
        //     Auth::guard('chair')->login($user, $request->boolean('remember'));
            
        //     // Arahkan ke konferensi pertama di mana ia adalah seorang Chair
        //     return redirect()->intended(Filament::getPanel('chair')->getUrl(
        //         $user->conferences()->where('role', ConferenceRole::Chair)->first()
        //     ));
        // }
        // // --- AKHIR PERBAIKAN ---

        // $request->session()->regenerate();
        // Auth::guard('web')->login($user, $request->boolean('remember'));
        // return redirect()->intended(Filament::getPanel('author')->getUrl());
        // // --- AKHIR LOGIKA BARU ---

        // throw ValidationException::withMessages([
        //     'email' => 'Akun Anda tidak memiliki hak akses untuk masuk.',
        // ]);

        /** HACK: Kode lama login per role */
        // session()->forget('url.intended');

        // // Regenerasi sesi untuk keamanan
        // $request->session()->regenerate();

        // if ($user->is_super_admin) {
        //     Auth::guard('admin')->login($user, $request->boolean('remember'));
        //     // Langsung arahkan ke tujuan yang benar, jangan pakai intended() lagi
        //     return redirect(Filament::getPanel('admin')->getUrl());
        // }

        // if ($user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->exists()) {
        //     Auth::guard('chair')->login($user, $request->boolean('remember'));
        //     $firstConference = $user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->first();
        //     // Langsung arahkan ke tujuan yang benar
        //     return redirect(Filament::getPanel('chair')->getUrl($firstConference));
        // }
        
        // throw \Illuminate\Validation\ValidationException::withMessages([
        //     'email' => 'This portal is only for Admin and Conference Chair.',
        // ]);

        /** Kode baru untuk login 1 halaman */
        // 4. Login Berhasil - Regenerasi Sesi
        session()->forget('url.intended');
        $request->session()->regenerate();

        // ============================================================
        //  TRAFFIC CONTROLLER (Menentukan Arah Redirect)
        // ============================================================

        // PRIORITAS 1: Super Admin -> Panel Admin
        if ($user->is_super_admin) {
            Auth::guard('admin')->login($user, $request->boolean('remember'));
            return redirect(Filament::getPanel('admin')->getUrl());
        }

        // Login ke guard 'web' (Kartu Akses Umum)
        // Ini mencakup Chair, Reviewer, dan Author
        Auth::guard('web')->login($user, $request->boolean('remember'));

        // PRIORITAS 2: Chair -> Panel Chair
        // Jika dia punya setidaknya satu konferensi sebagai Chair, arahkan ke sana
        if ($user->conferences()->where('role', ConferenceRole::Chair)->exists()) {
            // Kita perlu login ke guard chair juga (session swap nanti diurus middleware/controller)
            // Tapi karena kita pakai sistem switcher canggih, kita bisa arahkan ke JEMBATAN Switcher
            $firstConference = $user->conferences()->where('role', ConferenceRole::Chair)->first();
            
            // Arahkan ke Controller Switcher agar sesi diatur dengan benar!
            return redirect()->route('switch.chair', ['conference' => $firstConference->id]);
        }

        // PRIORITAS 3: Reviewer -> Panel Reviewer
        // Jika bukan Chair tapi Reviewer, arahkan ke Reviewer
        if ($user->conferences()->where('role', ConferenceRole::Reviewer)->exists()) {
            return redirect(Filament::getPanel('reviewer')->getUrl());
        }

        // PRIORITAS 4: Default -> Panel Author
        // Jika bukan apa-apa, pasti dia Author (atau user baru)
        return redirect(Filament::getPanel('author')->getUrl());
    }

    /**
     * Melakukan logout.
     */
    public function logout(Request $request)
    {
        // Logout dari semua guard yang mungkin aktif
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }
        if (Auth::guard('chair')->check()) {
            Auth::guard('chair')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}