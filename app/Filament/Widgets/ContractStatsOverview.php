<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContractResource;
use App\Models\Contract;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ContractStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected static ?int $sort = 1;
    
    public static function canView(): bool
    {
        return true;
    }
    
    protected function getStats(): array
    {
        $totalContracts = Contract::count();
        
        // Sử dụng tên cột thực tế từ CSDL để chứa giá trị hợp đồng
        $valueColumn = 'total_value'; // Tên cột thực tế từ bảng contracts
        
        $totalContractsValue = Contract::sum($valueColumn);
        
        $today = Carbon::today();
        
        // Hợp đồng còn hiệu lực - dựa trên ngày kết thúc phải lớn hơn hoặc bằng ngày hiện tại
        $activeContracts = Contract::where('end_date', '>=', $today)->count();
        $activeContractsValue = Contract::where('end_date', '>=', $today)->sum($valueColumn);
        
        // Hợp đồng hết hiệu lực - dựa trên ngày kết thúc phải nhỏ hơn ngày hiện tại
        $expiredContracts = Contract::where('end_date', '<', $today)->count();
        $expiredContractsValue = Contract::where('end_date', '<', $today)->sum($valueColumn);
        
        // Trạng thái thanh lý
        $liquidatedContracts = Contract::where('liquidation', 1)->count(); // 1 = đã thanh lý
        $liquidatedContractsValue = Contract::where('liquidation', 1)->sum($valueColumn);
        
        $unliquidatedContracts = Contract::where('liquidation', 0)->count(); // 0 = chưa thanh lý
        $unliquidatedContractsValue = Contract::where('liquidation', 0)->sum($valueColumn);
        
        // Thanh toán
        $paidInFull = Contract::where('pay', 2)->count(); // 2 = đã thanh toán đầy đủ
        $partiallyPaid = Contract::where('pay', 1)->count(); // 1 = đã thanh toán một phần
        $unpaidContracts = Contract::where('pay', 0)->count(); // 0 = chưa thanh toán
        
        // Tổng tiền đã thanh toán
        $totalPaid = DB::table('payments')
            ->sum('amount_paid'); // Tổng tiền đã thanh toán từ bảng payments
            
        $totalUnpaid = max(0, $totalContractsValue - $totalPaid);
        
        // Base URL for contract resource
        $contractsUrl = ContractResource::getUrl('index');
        
        // URL cho hợp đồng còn hiệu lực và hết hiệu lực sử dụng cấu trúc bộ lọc đúng
        $today_formatted = $today->format('Y-m-d');
        $activeContractsUrl = $contractsUrl . '?tableFilters[end_date][min]=' . $today_formatted;
        $expiredContractsUrl = $contractsUrl . '?tableFilters[end_date][max]=' . $today_formatted;

        return [
            Stat::make('TỔNG HỢP ĐỒNG', $totalContracts)
                ->description(number_format($totalContractsValue, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->chart([2, 3, 7, 5, 4, 6, $totalContracts])
                ->url($contractsUrl)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
                
            Stat::make('CÒN HIỆU LỰC', $activeContracts)
                ->description(number_format($activeContractsValue, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([1, 2, 3, 2, 3, 4, $activeContracts])
                ->url($activeContractsUrl)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
                
            Stat::make('HẾT HIỆU LỰC', $expiredContracts)
                ->description(number_format($expiredContractsValue, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger')
                ->chart([1, 2, 1, 0, 1, 2, $expiredContracts])
                ->url($expiredContractsUrl)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
                
            Stat::make('ĐÃ THANH LÝ', $liquidatedContracts)
                ->description(number_format($liquidatedContractsValue, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([0, 1, 0, 1, 1, 2, $liquidatedContracts])
                ->url($contractsUrl . '?tableFilters[liquidation][value]=1')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
                
            Stat::make('CHƯA THANH LÝ', $unliquidatedContracts)
                ->description(number_format($unliquidatedContractsValue, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->chart([2, 3, 4, 3, 4, 5, $unliquidatedContracts])
                ->url($contractsUrl . '?tableFilters[liquidation][value]=0')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
                
            Stat::make('ĐÃ THANH TOÁN', $paidInFull)
                ->description(number_format($totalPaid, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([0, 0, 1, 0, 1, 1, $paidInFull])
                ->url($contractsUrl . '?tableFilters[pay][value]=2')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
                
            Stat::make('CHƯA THANH TOÁN', $unpaidContracts)
                ->description(number_format($totalUnpaid, 0, ',', '.') . ' VND')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger')
                ->chart([2, 3, 2, 4, 3, 5, $unpaidContracts])
                ->url($contractsUrl . '?tableFilters[pay][value]=0')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
        ];
    }
}