<?php

namespace App\Filament\Chair\Resources\ConferenceHistoryResource\Pages;

use App\Filament\Chair\Resources\ConferenceHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageConferenceHistories extends ManageRecords
{
    protected static string $resource = ConferenceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
