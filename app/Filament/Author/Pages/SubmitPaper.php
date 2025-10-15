<?php

namespace App\Filament\Author\Pages;

use App\Enums\SubmissionStatus;
use App\Models\Conference;
use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

// Jika kamu punya Dashboard khusus panel Author, import di sini:
// use App\Filament\Author\Pages\Dashboard;

class SubmitPaper extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static string $view = 'filament.author.pages.submit-paper';
    protected static bool $shouldRegisterNavigation = false;

    public Conference $conference;
    public ?array $data = [];

    public function mount(Conference $conference): void
    {
        $this->conference = $conference;
        $this->form->fill();
    }

    public static function getRoutePath(): string
    {
        return '/conference-submit/{conference}';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('conference_id')
                    // gunakan closure agar aman kalau dipanggil sebelum mount
                    ->default(fn (self $livewire) => $livewire->conference->id),

                TextInput::make('title')
                    ->label('Judul Makalah')
                    ->required(),

                RichEditor::make('abstract')
                    ->label('Abstrak')
                    ->required(),

                TagsInput::make('keywords')
                    ->label('Kata Kunci')
                    ->required(),

                FileUpload::make('full_paper_path')
                    ->label('Unggah Makalah Lengkap (PDF/DOCX)')
                    // Ganti directory statis dengan closure dinamis
                    ->directory(fn () => 'conferences/' . $this->conference->slug . '/full-papers')
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Kirim Makalah')
                ->submit('submit'), // render <button type="submit">
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = auth()->id();
        $data['status'] = SubmissionStatus::Submitted;

        // Pastikan keywords diubah jadi string (misal dipisah koma)
        if (isset($data['keywords']) && is_array($data['keywords'])) {
            $data['keywords'] = implode(', ', $data['keywords']);
        }

        Submission::create($data);

        Notification::make()
            ->title('Makalah berhasil dikirim!')
            ->success()
            ->send();

        $this->redirect('/author');
    }

}
