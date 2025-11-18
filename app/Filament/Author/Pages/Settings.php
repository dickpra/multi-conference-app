<?php

namespace App\Filament\Author\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    // protected static ?string $navigationLabel = 'Pengaturan Profil';
    protected static string $view = 'filament.author.pages.settings';
    protected static ?int $navigationSort = 10; // Taruh di bagian bawah

    public function getTitle(): string
    {
        return __('Profile Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Profile Settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Profile Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Profile Settings');
    }

    public ?array $data = [];

    public function mount(): void
    {
        // Isi form dengan data user yang sedang login
        $this->form->fill(auth()->user()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('Nama Lengkap'))
                    ->required(),
                TextInput::make('country')
                    ->label(__('Negara Asal')),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('Password Baru'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->confirmed(), // Otomatis menambahkan field konfirmasi password
                TextInput::make('password_confirmation')
                    ->label(__('Konfirmasi Password Baru'))
                    ->password()
                    ->dehydrated(false),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Simpan Perubahan'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Ambil hanya data yang relevan untuk diupdate
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'country' => $data['country'],
        ];

        // Update password hanya jika diisi
        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        auth()->user()->update($updateData);

        Notification::make()
            ->title(__('Profil berhasil diperbarui'))
            ->success()
            ->send();

        // Refresh halaman untuk menampilkan data baru
        $this->redirect(static::getUrl());
    }
}