<?php

namespace App\Rules;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ApprovedUserStatus implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = User::where('email', $value)->first();

        // Jika user tidak ditemukan, biarkan aturan validasi lain (seperti 'exists') yang menanganinya.
        if (!$user) {
            return true; 
        }

        // Aturan ini lolos HANYA jika status user adalah 'approved'.
        return $user->status === \App\Enums\UserStatus::Approved->value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // Coba ambil email dari form login kustom ATAU form login bawaan Filament
        $email = request('email') ?? request('data.email');

        if ($email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                if ($user->status === UserStatus::Pending) {
                    return 'Akun Anda sedang menunggu persetujuan dari admin.';
                }
                if ($user->status === UserStatus::Rejected) {
                    return 'Akun Anda telah ditolak oleh admin.';
                }
            }
        }
        
        // Pesan default jika kondisi lain tidak terpenuhi
        return 'Akun ini tidak diizinkan untuk masuk atau kredensial salah.';
    }
}