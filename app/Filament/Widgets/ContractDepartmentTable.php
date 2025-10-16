<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Department;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class ContractDepartmentTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = '';
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Department::leftJoin('contracts', 'contracts.department_id', '=', 'departments.id')
                    ->select('departments.*')
                    ->selectRaw('
                        COUNT(contracts.id) as tong_hop_dong,
                        COALESCE(SUM(contracts.total_value), 0) as gia_tri,
                        
                        COUNT(CASE WHEN (contracts.end_date IS NULL OR contracts.end_date >= CURRENT_DATE) THEN 1 END) as con_hieu_luc,
                        COALESCE(SUM(CASE WHEN (contracts.end_date IS NULL OR contracts.end_date >= CURRENT_DATE) THEN contracts.total_value ELSE 0 END), 0) as gia_tri_con_hieu_luc,
                        
                        COUNT(CASE WHEN contracts.end_date < CURRENT_DATE THEN 1 END) as het_hieu_luc,
                        COALESCE(SUM(CASE WHEN contracts.end_date < CURRENT_DATE THEN contracts.total_value ELSE 0 END), 0) as gia_tri_het_hieu_luc,
                        
                        COUNT(CASE WHEN contracts.liquidation = 1 THEN 1 END) as da_thanh_ly,
                        COALESCE(SUM(CASE WHEN contracts.liquidation = 1 THEN contracts.total_value ELSE 0 END), 0) as gia_tri_da_thanh_ly,
                        
                        COUNT(CASE WHEN contracts.liquidation = 0 THEN 1 END) as chua_thanh_ly,
                        COALESCE(SUM(CASE WHEN contracts.liquidation = 0 THEN contracts.total_value ELSE 0 END), 0) as gia_tri_chua_thanh_ly,
                        
                        -- Đã thanh toán đầy đủ
                        COUNT(CASE WHEN contracts.pay = 2 THEN 1 END) as da_thanh_toan_day_du,
                        COALESCE(SUM(CASE WHEN contracts.pay = 2 THEN contracts.total_value ELSE 0 END), 0) as gia_tri_da_thanh_toan_day_du,
                        
                        -- Đã thanh toán một phần
                        COUNT(CASE WHEN contracts.pay = 1 THEN 1 END) as da_thanh_toan_mot_phan,
                        COALESCE(SUM(CASE WHEN contracts.pay = 1 THEN contracts.total_value ELSE 0 END), 0) as gia_tri_da_thanh_toan_mot_phan,
                        
                        -- Chưa thanh toán
                        COUNT(CASE WHEN contracts.pay = 0 THEN 1 END) as chua_thanh_toan,
                        COALESCE(SUM(CASE WHEN contracts.pay = 0 THEN contracts.total_value ELSE 0 END), 0) as gia_tri_chua_thanh_toan
                    ')
                    ->groupBy('departments.id', 'departments.name', 'departments.code', 'departments.description', 'departments.manager_id', 'departments.created_at', 'departments.updated_at');
            })
            ->columns([
                TextColumn::make('id')
                    ->label('STT')
                    ->sortable(),
                
                TextColumn::make('name')
                    ->label('Bộ phận')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),
                
                ColumnGroup::make('Tổng hợp đồng')
                    ->columns([
                        TextColumn::make('tong_hop_dong')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Còn hiệu lực')
                    ->columns([
                        TextColumn::make('con_hieu_luc')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_con_hieu_luc')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Hết hiệu lực')
                    ->columns([
                        TextColumn::make('het_hieu_luc')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_het_hieu_luc')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Đã thanh lý')
                    ->columns([
                        TextColumn::make('da_thanh_ly')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_da_thanh_ly')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Chưa thanh lý')
                    ->columns([
                        TextColumn::make('chua_thanh_ly')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_chua_thanh_ly')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Đã thanh toán đầy đủ')
                    ->columns([
                        TextColumn::make('da_thanh_toan_day_du')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_da_thanh_toan_day_du')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Đã thanh toán một phần')
                    ->columns([
                        TextColumn::make('da_thanh_toan_mot_phan')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_da_thanh_toan_mot_phan')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
                
                ColumnGroup::make('Chưa thanh toán')
                    ->columns([
                        TextColumn::make('chua_thanh_toan')
                            ->label('Số lượng')
                            ->alignCenter(),
                        TextColumn::make('gia_tri_chua_thanh_toan')
                            ->label('Giá trị')
                            ->money('VND'),
                    ]),
            ])
            ->paginated(false);
    }
}