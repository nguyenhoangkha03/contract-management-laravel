<?php

namespace App\Filament\Resources\ContractAttachmentResource\Pages;

use App\Filament\Resources\ContractAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractAttachment extends EditRecord
{
    protected static string $resource = ContractAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
