<?php

namespace App\Filament\Reviewer\Pages;

use App\Rules\ApprovedUserStatus;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    // Kita override method form untuk menambahkan validasi
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    // Di sinilah kita menambahkan aturan kustom kita
                    ->rules([new ApprovedUserStatus]), 
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }
}