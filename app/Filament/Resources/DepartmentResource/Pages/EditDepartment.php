<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected static ?string $title = 'Sửa Phòng Ban';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Xóa Phòng Ban')
                ->requiresConfirmation()
                ->modalHeading('Xóa Phòng Ban')
                ->modalSubheading('Bạn có chắc chắn muốn xóa phòng ban này không?')
                ->modalButton('Xóa'),
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
