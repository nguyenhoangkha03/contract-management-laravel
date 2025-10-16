<?php

namespace App\Filament\Resources\RevenueForecastResource\Pages;

use App\Filament\Resources\RevenueForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevenueForecasts extends ListRecords
{
    protected static string $resource = RevenueForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}