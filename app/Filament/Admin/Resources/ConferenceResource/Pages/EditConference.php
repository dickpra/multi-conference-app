<?php

namespace App\Filament\Admin\Resources\ConferenceResource\Pages;

use App\Filament\Admin\Resources\ConferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConference extends EditRecord
{
    protected static string $resource = ConferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
