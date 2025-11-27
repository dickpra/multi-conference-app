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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use App\Enums\Sdg;
use Filament\Forms\Components\TagsInput;

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
               // --- GANTI SELECT LAMA DENGAN INI ---
                    TagsInput::make('sdgs')
                        ->label(__('Fokus SDGs'))
                        ->placeholder(__('Pilih dari daftar atau ketik SDG custom baru...'))
                        // Ambil daftar teks dari Enum sebagai saran
                        ->suggestions(
                            collect(Sdg::cases())
                                ->map(fn ($sdg) => $sdg->getLabel())
                                ->values()
                                ->toArray()
                        )
                        ->columnSpanFull(),
                    // ------------------------------------
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
                Section::make(__('Informasi Pembayaran & Rekening (Internasional)'))
                    ->description(__('Lengkapi data ini untuk memudahkan pembayaran dari dalam dan luar negeri.'))
                    ->schema([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('registration_fee')
                                    ->label(__('Biaya Pendaftaran Author (Presenter)'))
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),

                                // INPUT BARU UNTUK PARTICIPANT
                                \Filament\Forms\Components\TextInput::make('participant_fee')
                                    ->label(__('Biaya Pendaftaran Participant (Listener)'))
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->helperText(__('Kosongkan jika gratis'))
                                    ->required(),
                            ]),

                        // --- DETAIL ORGANISASI ---
                        Grid::make(2)
                            ->schema([
                                TextInput::make('vat_number')
                                    ->label(__('VAT Number (NPWP)'))
                                    ->placeholder(__('Optional untuk lokal')),
                                
                                Textarea::make('postal_address')
                                    ->label(__('Alamat Pos Organisasi'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        // --- DETAIL BANK ---
                        Section::make(__('Detail Rekening Bank'))
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label(__('Nama Bank'))
                                    ->required(),
                                
                                TextInput::make('swift_code')
                                    ->label(__('SWIFT / BIC Code'))
                                    ->placeholder(__('Wajib untuk transfer internasional'))
                                    ->helperText(__('Kosongkan jika hanya menerima transfer lokal')),

                                TextInput::make('bank_account_number')
                                    ->label(__('Nomor Rekening'))
                                    ->required(),

                                TextInput::make('bank_account_holder')
                                    ->label(__('Nama Pemilik Rekening'))
                                    ->required(),

                                TextInput::make('bank_city')
                                    ->label(__('Kota Bank')),

                                Textarea::make('bank_account_address')
                                    ->label(__('Alamat Terdaftar Pemilik Rekening'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),

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