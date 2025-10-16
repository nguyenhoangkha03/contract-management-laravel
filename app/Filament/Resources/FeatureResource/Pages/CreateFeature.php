<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFeature extends CreateRecord
{
    protected static string $resource = FeatureResource::class;
    protected static ?string $title = 'Tạo Chức Năng';

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
