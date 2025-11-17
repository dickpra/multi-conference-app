<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($code)
    {
        // 1. Cek apakah kode bahasa valid (ada di database)
        $languageExists = Language::where('code', $code)->exists();

        if ($languageExists) {
            // 2. Simpan ke session
            Session::put('locale', $code);
        }

        // 3. Kembali ke halaman sebelumnya
        return redirect()->back();
    }
}