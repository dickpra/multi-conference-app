<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;


class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Definisikan secara manual guard yang ingin kita periksa
        $ourGuards = ['admin', 'chair', 'web'];

        foreach ($ourGuards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Jika user sudah login di salah satu guard kita,
                // dapatkan user dan lakukan redirect yang sesuai.
                $user = Auth::guard($guard)->user();

                if ($user->is_super_admin) {
                    return redirect(Filament::getPanel('admin')->getUrl());
                }

                // Cek relasi conferences untuk memastikan objek user valid
                if (method_exists($user, 'conferences') && $user->conferences()->exists()) {
                    $firstConference = $user->conferences->first();
                    return redirect(Filament::getPanel('chair')->getUrl($firstConference));
                }

                return redirect(Filament::getPanel('author')->getUrl());
            }
        }

        // Jika tidak ada user yang login di guard manapun yang kita cek,
        // izinkan untuk melanjutkan ke halaman yang dituju (halaman login).
        return $next($request);
    }
}
