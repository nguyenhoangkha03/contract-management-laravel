<?php

namespace App\Filament\Widgets;

use App\Models\ContractAnalytics;
use App\Services\ContractAnalyticsService;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ContractAnalyticsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Trend';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $service = app(ContractAnalyticsService::class);
        $report = $service->generatePerformanceReport();
        
        $monthlyTrend = $report['monthly_trend'];
        
        return [
            'datasets' => [
                [
                    'label' => 'Total Value',
                    'data' => array_column($monthlyTrend, 'total_value'),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Paid Amount',
                    'data' => array_column($monthlyTrend, 'paid_amount'),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => array_column($monthlyTrend, 'period'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}