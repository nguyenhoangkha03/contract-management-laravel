<?php

namespace App\Filament\Resources\ContractAnalyticsResource\Pages;

use App\Filament\Resources\ContractAnalyticsResource;
use App\Filament\Widgets\ContractAnalyticsStatsWidget;
use App\Filament\Widgets\ContractAnalyticsChartWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractAnalytics extends ListRecords
{
    protected static string $resource = ContractAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContractAnalyticsStatsWidget::class,
            ContractAnalyticsChartWidget::class,
        ];
    }
}