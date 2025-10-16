<?php

namespace App\Filament\Resources\ContractStatusResource\Pages;

use App\Filament\Resources\ContractStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractStatuses extends ListRecords
{
    protected static string $resource = ContractStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
