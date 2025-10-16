<?php

namespace App\Filament\Resources\RevenueForecastResource\Pages;

use App\Filament\Resources\RevenueForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevenueForecast extends EditRecord
{
    protected static string $resource = RevenueForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}