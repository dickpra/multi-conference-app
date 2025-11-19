<?php

namespace App\Filament\Chair\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Section;


class EditConferenceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.chair.pages.edit-conference-settings';
    protected static ?string $title = 'Conference Settings';
    protected ?string $subheading = 'Update your conference details here.';

    public ?array $data = [];

    public function mount(): void
    {
        // Ambil data dari tenant (conference) saat ini dan isi form
        $this->form->fill($this->getRecord()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('theme'),
                TextInput::make('location'),
                RichEditor::make('description')
                    ->label('Description / Call for Paper')
                    ->columnSpanFull(),
                FileUpload::make('paper_template_path')
                ->label(__('Unggah Template Paper (DOCX, PDF)'))
                // Ganti directory statis dengan closure dinamis
                ->directory(fn () => 'conferences/' . $this->getRecord()->slug . '/templates')
                ->acceptedFileTypes(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->columnSpanFull(),
                Section::make(__('Pengaturan Book of Abstracts'))
                ->description(__('Kustomisasi teks yang akan muncul di PDF Book of Abstracts.'))
                ->schema([
                    TextInput::make('book_title')
                        ->label(__('Judul Utama Buku'))
                        ->placeholder(__('Contoh: Book of Abstracts')),
                    TextInput::make('foreword_title')
                        ->label(__('Judul Kata Pengantar'))
                        ->placeholder(__('Contoh: Kata Pengantar')),
                    RichEditor::make('foreword')
                        ->label(__('Isi Kata Pengantar')),
                ]),
                DatePicker::make('start_date')->required(),
                DatePicker::make('end_date')->required(),
                FileUpload::make('logo')
                    ->image()
                    ->directory(fn () => 'conferences/' . $this->getRecord()->slug . '/logos'),
                TextInput::make('isbn_issn'),
                Section::make(__('Informasi Pembayaran & Rekening'))
                    ->description(__('Data ini akan ditampilkan ke Author setelah paper mereka diterima.'))
                    ->schema([
                        TextInput::make('registration_fee')
                            ->label(__('Biaya Pendaftaran (Rp)'))
                            ->prefix('Rp')
                            ->extraAttributes(['class' => 'text-right'])
                            
                            // Format ke Rupiah saat tampil di form
                            ->formatStateUsing(function ($state) {
                                if (!$state) return null;

                                return number_format((float) $state, 0, ',', '.'); 
                            })

                            // Simpan sebagai angka murni (hilangkan titik)
                            ->dehydrateStateUsing(function ($state) {
                                if (!$state) return 0;

                                // Hapus tanda titik
                                $raw = str_replace('.', '', $state);
                                // Ubah koma menjadi titik untuk desimal
                                $raw = str_replace(',', '.', $raw);

                                return is_numeric($raw) ? $raw : 0;
                            })

                            // Penting: tetap kirimkan data ke server meski disunting
                            ->dehydrated(true),

                        TextInput::make('bank_name')
                            ->label(__('Nama Bank'))
                            ->placeholder(__('Contoh: Bank Mandiri')),

                        TextInput::make('bank_account_number')
                            ->label(__('Nomor Rekening')),

                        TextInput::make('bank_account_holder')
                            ->label(__('Atas Nama')),
                    ])
                    ->columns(2),

            ])
            ->statePath('data')
            ->model($this->getRecord());
    }

    protected function getRecord(): Model
    {
        // Fungsi untuk mendapatkan record tenant (conference)
        return Filament::getTenant();
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
        $this->getRecord()->update($this->form->getState());

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}