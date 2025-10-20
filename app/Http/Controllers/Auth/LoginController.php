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
                'email' => __('auth.failed'),
            ]);
        }

        // --- LOGIKA PENGECEKAN STATUS BARU ---
        // --- LOGIKA PENGECEKAN STATUS YANG DIPERBAIKI ---
        // Gunakan ->value untuk perbandingan yang akurat
        if ($user->status !== UserStatus::Approved->value) {
            // Tentukan pesan error berdasarkan status
            $message = 'Akun ini tidak diizinkan untuk masuk.';
            if ($user->status === UserStatus::Pending->value) {
                $message = 'Akun Anda sedang menunggu persetujuan dari admin.';
            } elseif ($user->status === UserStatus::Rejected->value) {
                $message = 'Akun Anda telah ditolak oleh admin.';
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

        // Hapus "catatan" URL tujuan yang mungkin sudah usang
        session()->forget('url.intended');

        // Regenerasi sesi untuk keamanan
        $request->session()->regenerate();

        if ($user->is_super_admin) {
            Auth::guard('admin')->login($user, $request->boolean('remember'));
            // Langsung arahkan ke tujuan yang benar, jangan pakai intended() lagi
            return redirect(Filament::getPanel('admin')->getUrl());
        }

        if ($user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->exists()) {
            Auth::guard('chair')->login($user, $request->boolean('remember'));
            $firstConference = $user->conferences()->where('role', \App\Enums\ConferenceRole::Chair)->first();
            // Langsung arahkan ke tujuan yang benar
            return redirect(Filament::getPanel('chair')->getUrl($firstConference));
        }
        
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => 'Pintu masuk ini hanya untuk Admin dan Conference Chair.',
        ]);
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