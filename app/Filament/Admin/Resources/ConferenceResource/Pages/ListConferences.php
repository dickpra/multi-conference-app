<?php

namespace App\Filament\Admin\Resources\ConferenceResource\Pages;

use App\Filament\Admin\Resources\ConferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConferences extends ListRecords
{
    protected static string $resource = ConferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
