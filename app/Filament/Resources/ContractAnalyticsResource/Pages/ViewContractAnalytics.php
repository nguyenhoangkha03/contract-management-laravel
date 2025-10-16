<?php

namespace App\Filament\Resources\ContractAnalyticsResource\Pages;

use App\Filament\Resources\ContractAnalyticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContractAnalytics extends ViewRecord
{
    protected static string $resource = ContractAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}