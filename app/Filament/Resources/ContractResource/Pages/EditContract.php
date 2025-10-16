<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected static ?string $title = 'Sửa Hợp Đồng';

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

    // protected function getFormSchema(): array
    // {
    //     return [
    //         Tabs::make('Contract Tabs')
    //             ->tabs([
    //                 Tabs\Tab::make('Thông tin')
    //                     ->schema([
    //                         TextInput::make('contract_code')->label('Số hợp đồng')->required(),

    //                         Select::make('customer_id')
    //                             ->relationship('customer', 'name')
    //                             ->label('Khách hàng')
    //                             ->searchable()
    //                             ->required(),

    //                         Select::make('contract_type_id')
    //                             ->relationship('contractType', 'name')
    //                             ->label('Loại hợp đồng')
    //                             ->required(),

    //                         DatePicker::make('signed_date')->label('Ngày ký hợp đồng'),
    //                         DatePicker::make('effective_date')->label('Ngày hiệu lực'),
    //                         DatePicker::make('expiration_date')->label('Ngày hết hiệu lực'),

    //                         TextInput::make('value')->label('Giá trị hợp đồng')->numeric(),

    //                         Textarea::make('note')->label('Ghi chú'),
    //                     ]),

    //                 Tabs\Tab::make('Ghi chú')
    //                     ->schema([
    //                         Textarea::make('internal_note')->label('Ghi chú nội bộ'),
    //                     ]),

    //                 Tabs\Tab::make('Hóa đơn')
    //                     ->schema([
    //                         Placeholder::make('invoices_placeholder')
    //                             ->content('Danh sách hóa đơn sẽ được hiển thị ở đây (bước sau sẽ code)')
    //                     ]),

    //                 Tabs\Tab::make('Đính kèm')
    //                     ->schema([
    //                         Placeholder::make('attachments_placeholder')
    //                             ->content('File đính kèm sẽ được xử lý ở bước sau')
    //                     ]),
    //             ])
    //             ->columnSpanFull(),
    //     ];
    // }
}
