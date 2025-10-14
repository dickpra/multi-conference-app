<?php

namespace App\Filament\Chair\Resources\SubmissionResource\Pages;

use App\Filament\Chair\Resources\SubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubmission extends EditRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
