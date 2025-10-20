<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Menampilkan halaman form registrasi.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Menangani permintaan registrasi yang masuk.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'country' => ['required', 'string', 'max:255'], // <-- VALIDASI BARU
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        // Buat user dengan status 'pending' dan data negara
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country, // <-- SIMPAN DATA BARU
            'password' => Hash::make($request->password),
            'status' => UserStatus::Pending,
        ]);

        event(new Registered($user));

        session()->flash('registration_success', 'Registrasi Berhasil! Akun Anda telah dibuat dan sedang menunggu persetujuan dari Admin.');

        return redirect()->route('login');
    }
}