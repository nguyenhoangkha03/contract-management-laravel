<?php

namespace App\Filament\Resources\ContractNoteResource\Pages;

use App\Filament\Resources\ContractNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractNote extends EditRecord
{
    protected static string $resource = ContractNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
