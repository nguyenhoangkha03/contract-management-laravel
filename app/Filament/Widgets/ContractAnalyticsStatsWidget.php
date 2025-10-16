<?php

namespace App\Filament\Widgets;

use App\Models\ContractAnalytics;
use App\Services\ContractAnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ContractAnalyticsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $service = app(ContractAnalyticsService::class);
        $report = $service->generatePerformanceReport();
        
        return [
            Stat::make('Total Contracts', $report['total_contracts'])
                ->description('Active contracts')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
                
            Stat::make('Total Value', Number::currency($report['total_value'], 'VND'))
                ->description('Portfolio value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Total Paid', Number::currency($report['total_paid'], 'VND'))
                ->description(round($report['average_payment_percentage'], 1) . '% completion')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
                
            Stat::make('High Risk', $report['high_risk_contracts'])
                ->description('Contracts at risk')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}