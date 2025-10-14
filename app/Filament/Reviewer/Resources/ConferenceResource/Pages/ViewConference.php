<?php

namespace App\Filament\Reviewer\Resources\ConferenceResource\Pages;

use App\Filament\Reviewer\Resources\ConferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewConference extends ViewRecord
{
    protected static string $resource = ConferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
