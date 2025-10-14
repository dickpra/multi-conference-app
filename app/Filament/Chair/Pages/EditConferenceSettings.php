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
                    ->label('Unggah Template Paper (DOCX, PDF)')
                    ->directory('paper-templates')
                    ->acceptedFileTypes(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->columnSpanFull(),
                DatePicker::make('start_date')->required(),
                DatePicker::make('end_date')->required(),
                FileUpload::make('logo')->image()->directory('logos'),
                TextInput::make('isbn_issn'),
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
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
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