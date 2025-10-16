<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected static ?string $title = 'Sửa Quyền Hạn';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Xóa')
                ->requiresConfirmation()
                ->modalHeading('Xóa Quyền Hạn')
                ->modalSubheading('Bạn có chắc chắn muốn xóa quyền hạn này không?')
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
