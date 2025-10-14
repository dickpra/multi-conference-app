<?php

namespace App\Filament\Chair\Resources\SubmissionResource\Pages;

use App\Filament\Chair\Resources\SubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
