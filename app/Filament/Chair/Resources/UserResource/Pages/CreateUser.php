<?php

namespace App\Filament\Chair\Resources\UserResource\Pages;

use App\Filament\Chair\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
