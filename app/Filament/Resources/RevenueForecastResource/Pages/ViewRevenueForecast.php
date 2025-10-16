<?php

namespace App\Filament\Resources\RevenueForecastResource\Pages;

use App\Filament\Resources\RevenueForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRevenueForecast extends ViewRecord
{
    protected static string $resource = RevenueForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}