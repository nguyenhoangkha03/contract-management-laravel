<?php

namespace App\Filament\Resources\ContractStatusResource\Pages;

use App\Filament\Resources\ContractStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractStatus extends EditRecord
{
    protected static string $resource = ContractStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
