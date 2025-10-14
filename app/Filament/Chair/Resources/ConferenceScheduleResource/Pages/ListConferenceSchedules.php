<?php

namespace App\Filament\Chair\Resources\ConferenceScheduleResource\Pages;

use App\Filament\Chair\Resources\ConferenceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConferenceSchedules extends ListRecords
{
    protected static string $resource = ConferenceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
