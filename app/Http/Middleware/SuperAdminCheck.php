<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminCheck
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        // Jika user login dan bukan super admin
        if ($user && !$user->is_super_admin) {
            // Logout user tersebut
            Filament::auth()->logout();

            // Redirect ke login dengan error di field email
            return redirect(Filament::getPanel('admin')->getLoginUrl())
                ->withErrors([
                    'email' => 'You do not have permission to access this panel.',
                ]);
        }

        // Jika super admin atau belum login → lanjut
        return $next($request);
    }
}
