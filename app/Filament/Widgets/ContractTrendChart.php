<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ContractTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Biểu Đồ Hợp Đồng Theo Thời Gian';
    
    protected static ?int $sort = 2;
    
    // Mặc định hiển thị dữ liệu 12 tháng gần đây
    protected int $monthsToShow = 12;
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getData(): array
    {
        $data = $this->getContractData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Số lượng hợp đồng mới',
                    'data' => $data['counts'],
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Giá trị hợp đồng (triệu VND)',
                    'data' => $data['values'],
                    'fill' => false,
                    'borderColor' => 'rgb(54, 162, 235)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
    
    protected function getContractData(): array
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($this->monthsToShow - 1)->startOfMonth();
        
        // Lấy dữ liệu số lượng và giá trị hợp đồng theo tháng
        $contracts = Contract::select(
                DB::raw('YEAR(sign_date) as year'),
                DB::raw('MONTH(sign_date) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_value) as total_value')
            )
            ->where('sign_date', '>=', $startDate)
            ->where('sign_date', '<=', $endDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Chuẩn bị mảng dữ liệu trống cho tất cả các tháng
        $labels = [];
        $counts = [];
        $values = [];
        
        // Tạo mảng tháng để hiển thị
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $yearMonth = $currentDate->format('Y-m');
            $labels[] = $currentDate->format('m/Y');
            
            // Tìm dữ liệu cho tháng này
            $monthData = $contracts->first(function ($item) use ($currentDate) {
                return $item->year == $currentDate->year && $item->month == $currentDate->month;
            });
            
            $counts[] = $monthData ? $monthData->count : 0;
            $values[] = $monthData ? round($monthData->total_value / 1000000, 2) : 0; // Chuyển đổi sang triệu VND
            
            $currentDate->addMonth();
        }
        
        return [
            'labels' => $labels,
            'counts' => $counts,
            'values' => $values,
        ];
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Số lượng / Giá trị (triệu VND)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Tháng/Năm',
                    ],
                ],
            ],
        ];
    }
    
    // Thêm các tùy chọn cho người dùng
    protected function getFilters(): ?array
    {
        return [
            6 => '6 tháng gần đây',
            12 => '12 tháng gần đây', 
            24 => '24 tháng gần đây',
        ];
    }
    
    public function filterSelected(string $filterKey): void
    {
        $this->monthsToShow = (int) $filterKey;
    }
}