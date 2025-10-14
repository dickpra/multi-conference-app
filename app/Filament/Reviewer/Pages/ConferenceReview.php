<?php

namespace App\Filament\Reviewer\Pages;

use App\Enums\ConferenceRole;
use App\Models\Conference;
use App\Models\Submission;
use Filament\Pages\Page;
use Filament\Resources\Components\Tab;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\SubmissionStatus;

class ConferenceReview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.reviewer.pages.conference-review';

    public Conference $conference;

    // ... (method mount, getTitle, getRoutePath biarkan sama)

    public function mount(Conference $conference): void
    {
        $this->conference = $conference;
    }

    public function getTitle(): string | Htmlable
    {
        return 'Tugas Review: ' . $this->conference->name;
    }

    public static function getRoutePath(): string
    {
        return '/conference-review/{conference:slug}';
    }

    public function getTabs(): array
    {
        $userId = auth()->id();

        return [
            'all' => Tab::make('Semua Tugas'),

            'needs_review' => Tab::make('Membutuhkan Ulasan')
                ->modifyQueryUsing(function (Builder $query) use ($userId) {
                    // Tampilkan HANYA makalah yang belum pernah direview oleh user ini
                    $query->whereDoesntHave('reviews', fn (Builder $q) => $q->where('user_id', $userId));
                })
                ->badge(
                    $this->getTableQuery()->whereDoesntHave('reviews', fn(Builder $q) => $q->where('user_id', $userId))->count()
                ),
            
            'needs_rereview' => Tab::make('Butuh Ulasan Ulang')
                ->modifyQueryUsing(function (Builder $query) use ($userId) {
                    // Tampilkan makalah yang sudah direview, TAPI sudah diupdate (direvisi)
                    $query->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
                        ->whereRaw('submissions.updated_at > (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId]);
                })
                ->badge(
                    $this->getTableQuery()
                        ->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
                        ->whereRaw('submissions.updated_at > (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId])
                        ->count()
                ),

            'finished' => Tab::make('Selesai Diulas')
                ->modifyQueryUsing(function (Builder $query) use ($userId) {
                    // Tampilkan makalah yang sudah direview DAN tidak diupdate setelahnya
                    $query->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
                        ->whereRaw('submissions.updated_at <= (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId]);
                })
                ->badge(
                    $this->getTableQuery()
                        ->whereHas('reviews', fn (Builder $q) => $q->where('user_id', $userId))
                        ->whereRaw('submissions.updated_at <= (SELECT MAX(created_at) FROM reviews WHERE reviews.submission_id = submissions.id AND reviews.user_id = ?)', [$userId])
                        ->count()
                ),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('conference_id', $this->conference->id)
                    ->whereHas('assignedReviewers', function ($query) {
                        $query->where('user_id', auth()->id());
                    })
            )
            ->columns([
                TextColumn::make('title')->label('Judul Makalah')->wrap(),
                // --- TAMBAHKAN KEMBALI KOLOM STATUS INI ---
                TextColumn::make('review_status')
                    ->label('Status Ulasan Anda')
                    ->state(function (Submission $record): string {
                        if ($record->status === SubmissionStatus::RevisionSubmitted) {
                            return 'Butuh Ulasan Ulang';
                        }

                        $hasReviewed = $record->reviews()->where('user_id', auth()->id())->exists();
                        return $hasReviewed ? 'Selesai Diulas' : 'Membutuhkan Ulasan';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Selesai Diulas' => 'success',
                        'Butuh Ulasan Ulang' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Action::make('view_and_review')
                    ->label('Lihat & Review')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn (Submission $record): string => ReviewSubmission::getUrl(['submission' => $record])),
            ]);
    }
}