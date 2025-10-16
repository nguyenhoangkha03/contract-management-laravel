<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientTypeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Hợp Đồng Lao Động', Contract::query()->where('contract_type_id', 1)->count()),
            Stat::make('Hợp Đồng Cung Cấp Dịch Vụ', Contract::query()->where('contract_type_id', 2)->count()),
            Stat::make('Hợp Đồng Mua Bán', Contract::query()->where('contract_type_id', 3)->count()),
        ];
    }
}
