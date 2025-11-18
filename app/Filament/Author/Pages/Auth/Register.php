<?php

namespace App\Filament\Author\Pages\Auth;

use App\Enums\UserStatus;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    /**
     * Menimpa (override) seluruh proses registrasi untuk kontrol penuh.
     */
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        // Panggil method handleRegistration kustom kita
        $user = $this->handleRegistration($data);

        // Kirim notifikasi sukses
        Notification::make()
            ->title('Registration Successful!')
            ->body('Your account has been created and is pending approval by the Admin.')
            ->success()
            ->persistent() // Membuat notifikasi tetap ada sampai di-close manual
            ->send();
        
        // Kembalikan objek Response yang akan mengarahkan ke halaman login
        return app(RegistrationResponse::class);
    }

    /**
     * Override method ini untuk memastikan data status 'pending' disimpan
     * dan event Registered dikirim.
     */
    protected function handleRegistration(array $data): Model
    {
        $user = static::getUserModel()::create(array_merge($data, ['status' => UserStatus::Pending]));
        
        event(new Registered($user));

        return $user;
    }

    /**
     * Override method ini untuk memastikan tujuan redirect adalah halaman login.
     */
    protected function getRedirectUrl(): string
    {
        return filament()->getLoginUrl();
    }
}