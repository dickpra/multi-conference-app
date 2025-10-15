<?php

namespace App\Filament\Admin\Resources\ConferenceResource\Pages;

use App\Filament\Admin\Resources\ConferenceResource;
use App\Filament\Admin\Resources\ConferenceResource\Widgets\ConferenceStatsWidget; // <-- Import widget
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

    // --- TAMBAHKAN METHOD BARU INI ---
    protected function getHeaderWidgets(): array
    {
        return [
            ConferenceStatsWidget::class,
        ];
    }
}