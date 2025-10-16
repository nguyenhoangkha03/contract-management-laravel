<?php

namespace App\Filament\Resources\ContractAdjustmentResource\Pages;

use App\Filament\Resources\ContractAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractAdjustment extends EditRecord
{
    protected static string $resource = ContractAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
