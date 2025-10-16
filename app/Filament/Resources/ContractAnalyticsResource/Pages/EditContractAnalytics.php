<?php

namespace App\Filament\Resources\ContractAnalyticsResource\Pages;

use App\Filament\Resources\ContractAnalyticsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractAnalytics extends EditRecord
{
    protected static string $resource = ContractAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}