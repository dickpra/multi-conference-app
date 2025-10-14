<?php

namespace App\Filament\Chair\Resources\ConferenceScheduleResource\Pages;

use App\Filament\Chair\Resources\ConferenceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConferenceSchedule extends EditRecord
{
    protected static string $resource = ConferenceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
