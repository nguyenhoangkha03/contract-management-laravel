{{-- File: resources/views/filament/pages/dashboard.blade.php --}}
<x-filament-panels::page>
    {{-- Load all CSS files --}}
    <link rel="stylesheet" href="{{ asset('css/modern-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/enhanced-widgets.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-admin-layout.css') }}">
    

    {{-- Enhanced Stats Section --}}
    <div class="modern-stats-section mb-12">
        <livewire:app.filament.widgets.contract-stats-overview />
    </div>

    {{-- Enhanced Charts Section --}}
    <div class="modern-charts-section mb-16">
        <div class="charts-grid">
            <div class="chart-container-modern trend-chart">
                <livewire:app.filament.widgets.contract-trend-chart />
            </div>
            <div class="chart-container-modern value-chart">
                <livewire:app.filament.widgets.monthly-contract-value-chart />
            </div>
        </div>
    </div>

    {{-- Enhanced Table Section --}}
    <div class="modern-table-section mb-12">
        <div class="table-container-modern">
            <livewire:app.filament.widgets.contract-department-table />
        </div>
    </div>
</x-filament-panels::page>