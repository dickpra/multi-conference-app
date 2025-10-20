<?php

namespace App\Filament\Author\Widgets;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class AcceptedPapersWidget extends Widget
{
    protected static string $view = 'filament.author.widgets.accepted-papers-widget';

    public Collection $acceptedSubmissions;

    public function mount(): void
    {
        $this->acceptedSubmissions = Submission::where('user_id', auth()->id())
            ->where('status', SubmissionStatus::Accepted)
            ->get();
    }
}