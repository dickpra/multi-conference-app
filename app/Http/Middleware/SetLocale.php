<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Language;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah ada data bahasa di session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Jika tidak ada, cek database untuk bahasa default
            $defaultLang = Language::where('is_default', true)->first();
            if ($defaultLang) {
                App::setLocale($defaultLang->code);
            }
        }

        return $next($request);
    }
}