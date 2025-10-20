<?php

namespace App\Filament\Author\Pages;

use App\Enums\UserStatus;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model; // <-- Import the correct Model class

class RegisterUser extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
    
    // --- THIS IS THE FINAL, CORRECTED METHOD SIGNATURE ---
    protected function handleRegistration(array $data): Model
    {
        // This logic remains correct
        $user = static::getUserModel()::create(array_merge($data, ['status' => UserStatus::Pending]));
        
        return $user;
    }

    // This method correctly prevents auto-login
    protected function afterRegistration(Model $user): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Registrasi Berhasil!')
            ->body('Akun Anda telah dibuat dan sedang menunggu persetujuan dari admin.')
            ->success()
            ->send();
        
        // Redirect to the login page after showing the notification
        redirect(route('filament.author.auth.login'));
    }
}