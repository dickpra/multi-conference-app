<?php

namespace App\Filament\Author\Resources\AttendeeResource\Pages;

use App\Filament\Author\Resources\AttendeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendees extends ListRecords
{
    protected static string $resource = AttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
