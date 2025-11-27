<?php

namespace App\Filament\Author\Resources\AttendeeResource\Pages;

use App\Filament\Author\Resources\AttendeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendee extends EditRecord
{
    protected static string $resource = AttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
