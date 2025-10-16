<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeature extends EditRecord
{
    protected static string $resource = FeatureResource::class;

    protected static ?string $title = 'Sửa Chức Năng';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Xóa Chức Năng')
                ->requiresConfirmation()
                ->modalHeading('Xóa Chức Năng')
                ->modalSubheading('Bạn có chắc chắn muốn xóa chức năng này không?')
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
