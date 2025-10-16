<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyContractValueChart extends ChartWidget
{
    protected static ?string $heading = 'Giá Trị Hợp Đồng Theo Tháng';
    
    protected static ?int $sort = 4;
    
    // Mặc định hiển thị dữ liệu 6 tháng gần đây
    protected int $monthsToShow = 6;
    
    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getData(): array
    {
        $data = $this->getMonthlyContractValues();
        
        return [
            'datasets' => [
                [
                    'label' => 'Tổng giá trị (triệu VND)',
                    'data' => $data['values'],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
    
    protected function getMonthlyContractValues(): array
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($this->monthsToShow - 1)->startOfMonth();
        
        // Lấy dữ liệu tổng giá trị hợp đồng theo tháng
        $contractValues = Contract::select(
                DB::raw('YEAR(sign_date) as year'),
                DB::raw('MONTH(sign_date) as month'),
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
        $values = [];
        
        // Tạo mảng tháng để hiển thị
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $yearMonth = $currentDate->format('Y-m');
            $labels[] = $currentDate->format('m/Y');
            
            // Tìm dữ liệu cho tháng này
            $monthData = $contractValues->first(function ($item) use ($currentDate) {
                return $item->year == $currentDate->year && $item->month == $currentDate->month;
            });
            
            $values[] = $monthData ? round($monthData->total_value / 1000000, 2) : 0; // Chuyển đổi sang triệu VND
            
            $currentDate->addMonth();
        }
        
        return [
            'labels' => $labels,
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
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.dataset.label + ': ' + context.raw + ' triệu VND';
                        }"
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Giá trị (triệu VND)',
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
            3 => '3 tháng gần đây',
            6 => '6 tháng gần đây',
            12 => '12 tháng gần đây',
        ];
    }
    
    public function filterSelected(string $filterKey): void
    {
        $this->monthsToShow = (int) $filterKey;
    }
}