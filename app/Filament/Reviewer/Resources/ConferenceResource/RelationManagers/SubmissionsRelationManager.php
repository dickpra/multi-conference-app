<?php

namespace App\Filament\Reviewer\Resources\ConferenceResource\RelationManagers;

use App\Models\Submission;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Components\Tab;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';
    protected static ?string $title = 'Tugas Review';
    

    // public function getTabs(): array
    // {
    //     $userId = auth()->id();
    //     return [
    //         'all' => Tab::make('Semua Tugas'),

    //         'needs_review' => Tab::make('Membutuhkan Ulasan')
    //             ->modifyQueryUsing(function (Builder $query) use ($userId) {
    //                 $query->whereDoesntHave('reviews', fn (Builder $q) => $q->where('user_id', $userId));
    //             })
    //             ->badge(
    //                 $this->getTableQuery()->clone()->whereDoesntHave('reviews', fn(Builder $q) => $q->where('user_id', $userId))->count()
    //             ),
            
    //         'needs_rereview' => Tab::make('Butuh Ulasan Ulang')
    //             ->modifyQueryUsing(function (Builder $query) use ($userId) {
    //                 $query->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
    //                       ->whereRaw('submissions.updated_at > (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId]);
    //             })
    //             ->badge(
    //                 $this->getTableQuery()->clone()
    //                      ->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
    //                      ->whereRaw('submissions.updated_at > (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId])
    //                      ->count()
    //             ),

    //         'finished' => Tab::make('Selesai Diulas')
    //             ->modifyQueryUsing(function (Builder $query) use ($userId) {
    //                 $query->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
    //                       ->whereRaw('submissions.updated_at <= (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId]);
    //             })
    //             ->badge(
    //                 $this->getTableQuery()->clone()
    //                      ->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
    //                      ->whereRaw('submissions.updated_at <= (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId])
    //                      ->count()
    //             ),
    //     ];
    // }

    // --- METHOD UNTUK MEMBUAT TAB YANG DISEMPURNAKAN ---
    public function getTabs(): array
    {
        $userId = auth()->id();
        return [
            'needs_review' => Tab::make(__('Membutuhkan Ulasan'))
                ->modifyQueryUsing(function (Builder $query) use ($userId) {
                    // Tampilkan jika belum ada review dari user ini
                    $query->whereDoesntHave('reviews', fn(Builder $q) => $q->where('user_id', $userId));
                })
                ->badge($this->getTableQuery()->clone()->whereDoesntHave('reviews', fn(Builder $q) => $q->where('user_id', $userId))->count()),

            'finished' => Tab::make(__('Selesai Diulas'))
                ->modifyQueryUsing(function (Builder $query) use ($userId) {
                    // Tampilkan jika sudah ada review dari user ini
                    $query->whereHas('reviews', fn(Builder $q) => $q->where('user_id', $userId));
                })
                ->badge($this->getTableQuery()->clone()->whereHas('reviews', fn(Builder $q) => $q->where('user_id', $userId))->count()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                
                // --- KOLOM STATUS YANG DISEMPURNAKAN ---
                Tables\Columns\TextColumn::make('review_status')
                    ->label(__('Status Ulasan Anda'))
                    ->state(function (Submission $record): string {
                        $lastReview = $record->reviews()->where('user_id', auth()->id())->latest()->first();

                        if (! $lastReview) {
                            return __('Membutuhkan Ulasan');
                        }
                        
                        // if ($lastReview->created_at < $record->updated_at) {
                        //     return 'Butuh Ulasan Ulang';
                        // }

                        return __('Selesai Diulas');
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        __('Selesai Diulas') => 'success',
                        __('Butuh Ulasan Ulang') => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_and_review')
                    ->label(__('Lihat & Review'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn (Submission $record): string => \App\Filament\Reviewer\Pages\ReviewSubmission::getUrl(['submission' => $record])),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return $this->getOwnerRecord()->submissions()->getQuery()
            ->whereHas('assignedReviewers', fn ($q) => $q->where('user_id', auth()->id()));
    }
}