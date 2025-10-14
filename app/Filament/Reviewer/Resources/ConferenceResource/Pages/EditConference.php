<?php

namespace App\Filament\Reviewer\Resources\ConferenceResource\Pages;

use App\Filament\Reviewer\Resources\ConferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConference extends EditRecord
{
    protected static string $resource = ConferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
