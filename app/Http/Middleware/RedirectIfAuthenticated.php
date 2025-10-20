<?php

namespace App\Http\Middleware;

use App\Enums\ConferenceRole;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Cek apakah user sudah login di SALAH SATU guard kita
        if (Auth::guard('admin')->check()) {
            // Jika dia admin, paksa ke dashboard admin
            return redirect(Filament::getPanel('admin')->getUrl());
        }
        
        if (Auth::guard('chair')->check()) {
            $user = Auth::guard('chair')->user();
            // Jika dia chair, paksa ke dashboard chair
            $firstConference = $user->conferences()->where('role', ConferenceRole::Chair)->first();
            return redirect(Filament::getPanel('chair')->getUrl($firstConference));
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            // Jika dia user biasa (author/reviewer), paksa ke dashboardnya
             if ($user->conferences()->where('role', ConferenceRole::Reviewer)->exists()) {
                return redirect(Filament::getPanel('reviewer')->getUrl());
            }
            return redirect(Filament::getPanel('author')->getUrl());
        }

        // --- INI BAGIAN KUNCINYA ---
        // Jika setelah semua pengecekan di atas user TIDAK LOGIN di guard manapun,
        // maka izinkan dia untuk melanjutkan ke halaman yang dia tuju (halaman login).
        return $next($request);
    }
}