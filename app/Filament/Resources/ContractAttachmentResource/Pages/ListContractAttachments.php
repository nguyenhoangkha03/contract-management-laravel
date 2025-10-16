<?php

namespace App\Filament\Resources\ContractAttachmentResource\Pages;

use App\Filament\Resources\ContractAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractAttachments extends ListRecords
{
    protected static string $resource = ContractAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
