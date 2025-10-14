<?php

namespace App\Filament\Author\Pages;

use App\Models\Conference;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;


class ConferenceDetail extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.author.pages.conference-detail';

    public Conference $conference;

    public static function getRoutePath(): string
    {
        return '/conference-detail/{conference}';
    }

    public function mount(Conference $conference): void
    {
        $this->conference = $conference;
    }

    // Definisikan Infolist untuk menampilkan data
    public function conferenceInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->conference)
            ->schema([
                Section::make($this->conference->name)
                    ->description($this->conference->theme)
                    ->schema([
                        Grid::make(2)->schema([
                            ImageEntry::make('logo')->hiddenLabel(),
                            Grid::make(1)->schema([
                                TextEntry::make('location')->label('Lokasi'),
                                TextEntry::make('start_date')
                                    ->label('Tanggal')
                                    ->formatStateUsing(fn ($record) => 
                                        Carbon::parse($record->start_date)->format('d M') . ' - ' . Carbon::parse($record->end_date)->format('d M Y')
                                    ),
                                TextEntry::make('description')
                                    ->label('Deskripsi / CFP')
                                    ->html()
                                    ->columnSpanFull(),
                                TextEntry::make('paper_template_path')
                                    ->label('Unduh Template Paper')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('success')
                                    ->url(fn ($record) => Storage::url($record->paper_template_path))
                                    ->openUrlInNewTab()
                                    ->visible(fn ($record): bool => $record->paper_template_path !== null)
                                    ->extraAttributes(['class' => 'cursor-pointer text-primary underline']),
                            ]),
                        ]),
                    ]),
            ]);
    }

    // Tombol Submit Paper
    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit Paper Sekarang')
                ->icon('heroicon-o-document-arrow-up')
                ->url(fn (): string => SubmitPaper::getUrl(['conference' => $this->conference])),
        ];
    }
}