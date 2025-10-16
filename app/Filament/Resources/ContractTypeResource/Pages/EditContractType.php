<?php

namespace App\Filament\Resources\ContractTypeResource\Pages;

use App\Filament\Resources\ContractTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContractType extends EditRecord
{
    protected static string $resource = ContractTypeResource::class;

    protected static ?string $title = 'Sửa Loại Hợp Đồng';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Xóa'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Lưu'),

            $this->getCancelFormAction()
                ->label('Hủy'),
        ];
    }
}
