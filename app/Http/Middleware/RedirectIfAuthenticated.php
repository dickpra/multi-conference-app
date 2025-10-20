<?php

namespace App\Http\Middleware;

use App\Enums\ConferenceRole;
use App\Providers\RouteServiceProvider;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Cek guard 'admin'
        if (Auth::guard('admin')->check()) {
            return redirect(Filament::getPanel('admin')->getUrl());
        }
        
        // Cek guard 'chair'
        if (Auth::guard('chair')->check()) {
            $user = Auth::guard('chair')->user();
            if ($user && method_exists($user, 'conferences')) {
                $firstConference = $user->conferences()->where('role', ConferenceRole::Chair)->first();
                if ($firstConference) {
                    return redirect(Filament::getPanel('chair')->getUrl($firstConference));
                }
            }
        }
        
        // Cek guard 'web' (untuk Author dan Reviewer)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user) {
                // Prioritaskan Reviewer
                if (method_exists($user, 'conferences') && $user->conferences()->where('role', ConferenceRole::Reviewer)->exists()) {
                    return redirect(Filament::getPanel('reviewer')->getUrl());
                }
                // Jika bukan reviewer, maka dia adalah author
                return redirect(Filament::getPanel('author')->getUrl());
            }
        }

        // --- INI BAGIAN KUNCINYA ---
        // Jika setelah semua pengecekan di atas user TIDAK LOGIN di guard manapun,
        // maka izinkan dia untuk melanjutkan ke halaman yang dia tuju (halaman login).
        return $next($request);
    }
}