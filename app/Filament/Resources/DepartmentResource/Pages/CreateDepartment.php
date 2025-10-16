<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;
    protected static ?string $title = 'Tạo Phòng Ban';

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Lưu'),

            \Filament\Actions\Action::make('resetForm')
                ->label('Tạo Lại')
                ->color('gray')
                ->action(fn() => $this->form->fill()),

            $this->getCancelFormAction()
                ->label('Hủy'),
        ];
    }
}
