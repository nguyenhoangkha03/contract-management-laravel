<?php

namespace App\Filament\Resources\ContractAdjustmentResource\Pages;

use App\Filament\Resources\ContractAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractAdjustments extends ListRecords
{
    protected static string $resource = ContractAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
