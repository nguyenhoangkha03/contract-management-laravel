<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;
    protected static ?string $title = 'Sửa Khách Hàng';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Xóa')
                ->label('Xóa Khách Hàng')
                ->requiresConfirmation()
                ->modalHeading('Xóa Chức Năng')
                ->modalSubheading('Bạn có chắc chắn muốn xóa chức năng này không?')
                ->modalButton('Xóa'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Lưu thay đổi')
                ->submit('save'),
            $this->getCancelFormAction()
                ->label('Hủy'),
        ];
    }
}
