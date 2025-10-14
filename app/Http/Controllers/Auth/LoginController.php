<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        if ($user->is_super_admin) {
            $request->session()->regenerate();
            Auth::guard('admin')->login($user, $request->boolean('remember'));
            return redirect()->intended(Filament::getPanel('admin')->getUrl());
        }

        // --- LOGIKA BARU UNTUK CHAIR ---
        $conferences = $user->conferences; // Ambil semua konferensi yang bisa diakses user

        if ($conferences->isNotEmpty()) { // Cek apakah user punya akses ke setidaknya satu konferensi
            $request->session()->regenerate();
            Auth::guard('chair')->login($user, $request->boolean('remember'));

            // Ambil konferensi pertama sebagai tujuan redirect
            $firstConference = $conferences->first();

            // Arahkan ke URL dashboard dari konferensi tersebut, yang sudah lengkap dengan slug
            return redirect()->intended(
                Filament::getPanel('chair')->getUrl($firstConference)
            );
        }

        $request->session()->regenerate();
        Auth::guard('web')->login($user, $request->boolean('remember'));
        return redirect()->intended(Filament::getPanel('author')->getUrl());
        // --- AKHIR LOGIKA BARU ---

        throw ValidationException::withMessages([
            'email' => 'Akun Anda tidak memiliki hak akses untuk masuk.',
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