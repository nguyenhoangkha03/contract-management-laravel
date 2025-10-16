<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

    protected static ?string $title = 'Tạo Hợp Đồng';

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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('detail', ['record' => $this->record]);
    }
}
