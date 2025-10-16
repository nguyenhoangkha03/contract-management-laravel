<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Contract;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Facades\Filament;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\TextInput;

class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    public function getTitle(): string
    {
        return $this->record->client->name ?? 'Chi tiết hợp đồng';
    }

    public function getSubheading(): string
    {
        return $this->record->contract_number ?? '';
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('sao_chép')
                ->label('Sao chép')
                ->icon('heroicon-o-clipboard-document')
                ->color('primary')
                ->action(function () {
                    // Clone contract logic
                    $this->redirect(ContractResource::getUrl('create'));
                }),

            PageAction::make('xóa_hợp_đồng')
                ->label('Xóa hợp đồng')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->delete();
                    $this->redirect(ContractResource::getUrl('index'));
                }),

            PageAction::make('thiết_lập')
                ->label('Thiết lập')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->action(function () {
                    // Settings logic
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->columns(12)
                    ->schema([
                        Tabs::make('status_tabs')
                            ->tabs([
                                Tabs\Tab::make('Chờ duyệt')
                                    ->icon('heroicon-o-check-circle')
                                    ->badge('active')
                                    ->schema([])
                                    ->extraAttributes(['style' => 'flex-grow: 1; min-width: 100px; text-align: center;']),
                                Tabs\Tab::make('Đã duyệt')
                                    ->schema([])
                                    ->extraAttributes(['style' => 'flex-grow: 1; min-width: 100px; text-align: center;']),
                                Tabs\Tab::make('Dự thảo')
                                    ->schema([])
                                    ->extraAttributes(['style' => 'flex-grow: 1; min-width: 100px; text-align: center;']),
                                Tabs\Tab::make('Thương thảo')
                                    ->schema([])
                                    ->extraAttributes(['style' => 'flex-grow: 1; min-width: 100px; text-align: center;']),
                                Tabs\Tab::make('Trình ký')
                                    ->schema([])
                                    ->extraAttributes(['style' => 'flex-grow: 1; min-width: 100px; text-align: center;']),
                                Tabs\Tab::make('Đã ký')
                                    ->schema([])
                                    ->extraAttributes(['style' => 'flex-grow: 1; min-width: 100px; text-align: center;']),
                            ])
                            ->columnSpanFull()
                            ->activeTab(0)
                            ->extraAttributes([
                                'class' => 'bg-gray-800 text-white py-2 flex justify-between items-center w-full gap-2'
                            ]),

                        // Left sidebar - Contract details
                        Section::make()
                            ->columnSpan(3)
                            ->schema([
                                TextEntry::make('client.name')
                                    ->label('')
                                    ->formatStateUsing(fn() => 'Cửa hàng Bắc Lan')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Large),

                                TextEntry::make('contract_code')
                                    ->label('Số hợp đồng')
                                    ->formatStateUsing(fn() => $this->record->contract_number ?? '-'),

                                TextEntry::make('contractType.name')
                                    ->label('Loại hợp đồng')
                                    ->formatStateUsing(fn() => 'Hợp đồng sản xuất'),

                                TextEntry::make('signed_date')
                                    ->label('Ngày ký')
                                    ->formatStateUsing(fn() => $this->record->signed_date ? date('d/m/Y', strtotime($this->record->signed_date)) : '22/12/2020'),

                                TextEntry::make('effective_date')
                                    ->label('Ngày hiệu lực')
                                    ->formatStateUsing(fn() => $this->record->effective_date ? date('d/m/Y', strtotime($this->record->effective_date)) : '22/12/2020'),

                                TextEntry::make('expiry_date')
                                    ->label('Ngày hết hiệu lực')
                                    ->formatStateUsing(fn() => $this->record->expiry_date ? date('d/m/Y', strtotime($this->record->expiry_date)) : '28/02/2021'),

                                TextEntry::make('value')
                                    ->label('Giá trị hợp đồng')
                                    ->formatStateUsing(fn() => number_format($this->record->value ?? 100000000) . ' VNĐ'),

                                TextEntry::make('paid_value')
                                    ->label('Tổng giá trị đã thanh toán')
                                    ->formatStateUsing(fn() => number_format($this->record->paid_value ?? 10000000) . ' VNĐ'),

                                TextEntry::make('unpaid_value')
                                    ->label('Giá trị chưa thanh toán')
                                    ->formatStateUsing(fn() => number_format($this->record->unpaid_value ?? 90000000) . ' VNĐ'),

                                // Progress bar
                                TextEntry::make('progress')
                                    ->label('Tiến độ thanh toán')
                                    ->formatStateUsing(function () {
                                        $paid = $this->record->paid_value ?? 10000000;
                                        $total = $this->record->value ?? 100000000;
                                        $percentage = ($paid / $total) * 100;
                                        return '<div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: ' . $percentage . '%"></div>
                                                </div>';
                                    })
                                    ->html(),

                                // Download button
                                \Filament\Infolists\Components\Actions::make([
                                    Action::make('download_contract')
                                        ->label('Tải hợp đồng')
                                        ->icon('heroicon-o-document-arrow-down')
                                        ->button()
                                        ->color('primary')
                                        ->url('#'),
                                ])
                                    ->columnSpanFull(),

                                // Responsible persons section
                                TextEntry::make('responsible_persons_heading')
                                    ->label('Người phụ trách (5)')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('responsible_persons')
                                    ->hiddenLabel()
                                    ->html()
                                    ->formatStateUsing(function () {
                                        $users = [
                                            [
                                                'name' => 'Phạm Hồng Quân',
                                                'role' => 'Kinh doanh',
                                                'email' => 'fw@quanph'
                                            ],
                                            [
                                                'name' => 'Lê Thị Thanh Ly',
                                                'role' => 'Hợp đồng',
                                                'email' => 'mtw@lytt'
                                            ]
                                        ];

                                        $html = '';
                                        foreach ($users as $user) {
                                            $html .= '
                                            <div class="flex items-center gap-4 py-2 border-b border-gray-200">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-gray-700">' . substr($user['name'], 0, 1) . '</span>
                                                </div>
                                                <div>
                                                    <div class="font-medium">' . $user['name'] . '</div>
                                                    <div class="text-gray-500 text-sm">' . $user['role'] . '</div>
                                                    <div class="text-gray-500 text-xs">' . $user['email'] . '</div>
                                                </div>
                                            </div>';
                                        }

                                        return $html;
                                    }),
                            ]),

                        // Right section - Content tabs
                        Section::make()
                            ->columnSpan(9)
                            ->schema([
                                TextEntry::make('contract_status')
                                    ->label('')
                                    ->formatStateUsing(fn() => 'Hợp đồng đã xút')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Large),

                                Tabs::make('content_tabs')
                                    ->tabs([
                                        Tabs\Tab::make('Thông tin')
                                            ->icon('heroicon-o-information-circle')
                                            ->schema([
                                                TextEntry::make('information')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Thông tin chi tiết về hợp đồng sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Hàng hóa')
                                            ->icon('heroicon-o-shopping-bag')
                                            ->schema([
                                                TextEntry::make('products')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Danh sách hàng hóa sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Trao đổi (0)')
                                            ->icon('heroicon-o-chat-bubble-left-right')
                                            ->schema([
                                                TextEntry::make('messages')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Các trao đổi về hợp đồng sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Ghi chú (0)')
                                            ->icon('heroicon-o-pencil-square')
                                            ->schema([
                                                TextEntry::make('notes')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Các ghi chú về hợp đồng sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Thanh toán (1)')
                                            ->icon('heroicon-o-currency-dollar')
                                            ->schema([
                                                TextEntry::make('payment_info')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Thông tin thanh toán sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Hóa đơn (0)')
                                            ->icon('heroicon-o-document-text')
                                            ->schema([
                                                \Filament\Infolists\Components\Actions::make([
                                                    Action::make('create_invoice')
                                                        ->label('+ Hóa đơn')
                                                        ->button()
                                                        ->color('success')
                                                        ->url('#')
                                                ]),
                                                TextEntry::make('invoices')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Danh sách hóa đơn sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Đính kèm (0)')
                                            ->icon('heroicon-o-paper-clip')
                                            ->schema([
                                                TextEntry::make('attachments')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Các tài liệu đính kèm sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Hoạt động (2)')
                                            ->icon('heroicon-o-clock')
                                            ->schema([
                                                TextEntry::make('activities')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Nhật ký hoạt động sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                        Tabs\Tab::make('Công việc (0)')
                                            ->icon('heroicon-o-clipboard-document-list')
                                            ->schema([
                                                TextEntry::make('tasks')
                                                    ->hiddenLabel()
                                                    ->html()
                                                    ->formatStateUsing(fn() => '<div class="text-gray-600">Danh sách công việc sẽ hiển thị ở đây.</div>')
                                            ])
                                            ->extraAttributes(['style' => 'flex-grow: 1; min-width: 80px; text-align: center;']),
                                    ])
                                    ->columnSpanFull()
                                    ->activeTab(0)
                                    ->extraAttributes([
                                        'class' => 'flex justify-between items-center w-full gap-2'
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
