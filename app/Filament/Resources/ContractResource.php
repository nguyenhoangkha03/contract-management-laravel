<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContractExport;
use App\Filament\Resources\ContractResource\Pages\ContractDetail;
use App\Filament\Resources\ContractResource\Pages\ViewContract;
use App\Models\ContractStatus;
use App\Models\ContractType;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Filament\Tables\Filters\Filter;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralLabel = 'Hợp Đồng';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('contract_number')
                    ->label('Số hợp đồng')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Nhập số hợp đồng'),
                Forms\Components\Select::make('client_id')
                    ->label('Khách hàng')
                    ->relationship('client', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $client = \App\Models\Client::find($state);
                        $set('client_address', $client?->address);
                    }),
                Forms\Components\Select::make('contract_type_id')
                    ->label('Loại hợp đồng')
                    ->relationship('contractType', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $contractType = ContractType::with(['supervisors.user', 'supervisors.role'])->find($state);

                        $getNameByRoleCode = function ($code) use ($contractType) {
                            return $contractType
                                ?->supervisors
                                ?->first(fn($s) => $s->role?->code === $code)
                                ?->user?->name ?? '';
                        };

                        $set('manager_name', $getNameByRoleCode('quanly'));
                        $set('contract_staff_name', $getNameByRoleCode('hopdong'));
                        $set('accounting_name', $getNameByRoleCode('ketoan'));
                        $set('deployment_name', $getNameByRoleCode('trienkhai'));
                        $set('follower_name', $getNameByRoleCode('theodoi'));
                    }),
                Forms\Components\TextInput::make('client_address')
                    ->label('Địa chỉ giao hàng')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\DatePicker::make('sign_date')
                    ->label('Ngày ký hợp đồng')
                    ->required()
                    ->rules([])
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Ngày hợp đồng có hiệu lực')
                    ->required()
                    ->rules([])
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Ngày hợp động hết hiệu lực')
                    ->required()
                    ->rules([])
                    ->displayFormat('d/m/Y'),
                Forms\Components\TextInput::make('total_value')
                    ->label('Giá trị hợp đồng')
                    ->numeric()
                    ->required()
                    ->minValue(0, 'Giá trị hợp đồng không được âm')
                    ->mask(RawJs::make('$money($input)'))
                    ->prefix('VND')
                    ->placeholder('0')
                    ->hint('Nhập giá trị không có dấu phẩy'),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Bộ phận kinh doanh')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('sales_employee_id')
                    ->relationship('salesEmployee', 'name')
                    ->label('Nhân viên kinh doanh')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('manager_name')
                    ->label('Người quản lý')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('contract_staff_name')
                    ->label('Nhân viên hợp đồng')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('accounting_name')
                    ->label('Nhân viên kế toán')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('deployment_name')
                    ->label('Nhân viên triển khai')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('follower_name')
                    ->label('Người theo dõi')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('contract_status_id')
                    ->relationship('contractStatus', 'name')
                    ->label('Trạng thái')
                    ->required()
                    ->default(function () {
                        return ContractStatus::where('code', 'choduyet')->first()?->id;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stt')
                    ->label('STT')
                    ->state(fn($livewire) => $livewire->getTableRecordsPerPage() * ($livewire->getTablePage() - 1))
                    ->formatStateUsing(fn($state, $record, $livewire) => $state + $livewire->getTableRecords()->search($record) + 1),
                Tables\Columns\TextColumn::make('contract_number')->sortable()->label('Số Hợp Đồng'),
                Tables\Columns\TextColumn::make('client.name')->sortable()->label('Khách hàng')->color('primary'),
                Tables\Columns\TextColumn::make('contractType.name')->label('Loại Hợp Đồng')->sortable(),
                Tables\Columns\TextColumn::make('salesEmployee.name')->label('Nhân Viên Kinh Doanh')->sortable(),
                Tables\Columns\TextColumn::make('contractStatus.name')->label('Trạng Thái Xử Lý'),
                Tables\Columns\TextColumn::make('total_value')->label('Giá Trị Hợp Đồng')
                    ->money('VND')
                    ->sortable(),
            ])
            ->filters([
                // Thêm bộ lọc ngày kết thúc
                Filter::make('end_date')
                    ->form([
                        DatePicker::make('min')
                            ->label('Từ ngày'),
                        DatePicker::make('max')
                            ->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '>=', $date),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['min'] ?? null) {
                            $indicators['min'] = 'Ngày hết hạn từ: ' . Carbon::parse($data['min'])->format('d/m/Y');
                        }

                        if ($data['max'] ?? null) {
                            $indicators['max'] = 'Ngày hết hạn đến: ' . Carbon::parse($data['max'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('pay')
                    ->label('Trạng thái thanh toán')
                    ->options([
                        '2' => 'Đã thanh toán đầy đủ',
                        '1' => 'Đã thanh toán một phần',
                        '0' => 'Chưa thanh toán',
                    ]),

                // Add liquidation status filter
                Tables\Filters\SelectFilter::make('liquidation')
                    ->label('Trạng thái thanh lý')
                    ->options([
                        '1' => 'Đã thanh lý',
                        '0' => 'Chưa thanh lý',
                    ]),
            ])
            ->actions([
                // Action::make('View')
                //     ->label('Xem chi tiết')
                //     ->icon('heroicon-o-eye')
                //     ->openUrlInNewTab(),
                // Action::make('Export Word')
                //     ->url(fn(Contract $record) => "/export-word/{$record->id}")
                //     ->openUrlInNewTab()
                //     ->label('Export Word')
                //     ->icon('heroicon-o-document'),
                // Action::make('Export Excel')
                //     ->url('/export-excel')
                //     ->openUrlInNewTab()
                //     ->label('Export Excel')
                //     ->icon('heroicon-o-document-arrow-down'),
                Action::make('viewDetail')
                    ->label('Chi tiết')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => ContractDetail::getUrl(['record' => $record->getKey()])),
                // ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->label('Sửa'),
                Tables\Actions\DeleteAction::make()->label('Xóa')
                    ->requiresConfirmation()
                    ->modalHeading('Xóa Hợp Đồng')
                    ->modalSubheading('Bạn có chắc chắn muốn xóa hợp đồng này không?')
                    ->modalButton('Xóa')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            // 'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
            'detail' => Pages\ContractDetail::route('/{record}/detail'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hợp Đồng';
    }

    public static function getNavigationLabel(): string
    {
        return 'Hợp Đồng';
    }
}
