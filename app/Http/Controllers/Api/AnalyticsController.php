<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractAnalytics;
use App\Models\RevenueForecast;
use App\Services\ContractAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected ContractAnalyticsService $analyticsService;

    public function __construct(ContractAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get contract analytics overview
     */
    public function overview(Request $request): JsonResponse
    {
        $periodType = $request->get('period_type', 'monthly');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $report = $this->analyticsService->generatePerformanceReport($periodType, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get contract analytics for specific contract
     */
    public function contractAnalytics(Request $request, int $contractId): JsonResponse
    {
        $contract = Contract::findOrFail($contractId);
        $periodType = $request->get('period_type', 'monthly');

        $analytics = ContractAnalytics::forContract($contractId)
            ->forPeriod($periodType)
            ->orderBy('period_date', 'desc')
            ->limit(12)
            ->get();

        $currentMetrics = $this->analyticsService->calculateContractMetrics($contract, $periodType);

        return response()->json([
            'success' => true,
            'data' => [
                'contract' => $contract,
                'current_metrics' => $currentMetrics,
                'historical_data' => $analytics,
            ]
        ]);
    }

    /**
     * Get performance metrics dashboard
     */
    public function performanceDashboard(Request $request): JsonResponse
    {
        $periodType = $request->get('period_type', 'monthly');
        $limit = $request->get('limit', 10);

        // Top performing contracts
        $topPerformers = ContractAnalytics::forPeriod($periodType)
            ->byPerformance('excellent')
            ->with('contract')
            ->orderBy('payment_percentage', 'desc')
            ->limit($limit)
            ->get();

        // High risk contracts
        $highRiskContracts = ContractAnalytics::forPeriod($periodType)
            ->highRisk()
            ->with('contract')
            ->orderBy('risk_score', 'desc')
            ->limit($limit)
            ->get();

        // Overdue contracts
        $overdueContracts = ContractAnalytics::forPeriod($periodType)
            ->overdue()
            ->with('contract')
            ->orderBy('overdue_days', 'desc')
            ->limit($limit)
            ->get();

        // Performance trends
        $performanceTrend = ContractAnalytics::forPeriod($periodType)
            ->where('period_date', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('period_date, performance_status, COUNT(*) as count')
            ->groupBy('period_date', 'performance_status')
            ->orderBy('period_date')
            ->get()
            ->groupBy('period_date');

        return response()->json([
            'success' => true,
            'data' => [
                'top_performers' => $topPerformers,
                'high_risk_contracts' => $highRiskContracts,
                'overdue_contracts' => $overdueContracts,
                'performance_trend' => $performanceTrend,
            ]
        ]);
    }

    /**
     * Get revenue forecast data
     */
    public function revenueForecast(Request $request): JsonResponse
    {
        $forecastType = $request->get('forecast_type', 'monthly');
        $limit = $request->get('limit', 12);

        $forecasts = RevenueForecast::forType($forecastType)
            ->orderBy('forecast_date', 'desc')
            ->limit($limit)
            ->get();

        // Calculate accuracy metrics
        $accuracyStats = RevenueForecast::forType($forecastType)
            ->withActuals()
            ->selectRaw('
                AVG(CASE 
                    WHEN actual_revenue > 0 THEN 
                        100 - (ABS(actual_revenue - predicted_revenue) / actual_revenue * 100)
                    ELSE 0 
                END) as average_accuracy,
                COUNT(*) as total_forecasts,
                COUNT(CASE WHEN actual_revenue IS NOT NULL THEN 1 END) as forecasts_with_actuals
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'forecasts' => $forecasts,
                'accuracy_stats' => $accuracyStats,
            ]
        ]);
    }

    /**
     * Generate new revenue forecast
     */
    public function generateForecast(Request $request): JsonResponse
    {
        $request->validate([
            'forecast_type' => 'required|in:monthly,quarterly,yearly',
            'periods_ahead' => 'required|integer|min:1|max:24',
        ]);

        $forecasts = $this->analyticsService->generateRevenueForecast(
            $request->get('forecast_type'),
            $request->get('periods_ahead')
        );

        foreach ($forecasts as $forecast) {
            $this->analyticsService->saveRevenueForecast($forecast);
        }

        return response()->json([
            'success' => true,
            'message' => 'Revenue forecast generated successfully',
            'data' => $forecasts
        ]);
    }

    /**
     * Update contract analytics
     */
    public function updateContractAnalytics(Request $request, int $contractId): JsonResponse
    {
        $contract = Contract::findOrFail($contractId);
        $periodType = $request->get('period_type', 'monthly');

        $analytics = $this->analyticsService->updateContractAnalytics($contract, $periodType);

        return response()->json([
            'success' => true,
            'message' => 'Contract analytics updated successfully',
            'data' => $analytics
        ]);
    }

    /**
     * Get analytics trends
     */
    public function trends(Request $request): JsonResponse
    {
        $periodType = $request->get('period_type', 'monthly');
        $months = $request->get('months', 12);
        $startDate = Carbon::now()->subMonths($months);

        $trends = ContractAnalytics::forPeriod($periodType)
            ->where('period_date', '>=', $startDate)
            ->selectRaw('
                period_date,
                SUM(total_value) as total_value,
                SUM(paid_amount) as paid_amount,
                SUM(outstanding_amount) as outstanding_amount,
                AVG(payment_percentage) as avg_payment_percentage,
                AVG(risk_score) as avg_risk_score,
                COUNT(*) as contract_count,
                COUNT(CASE WHEN is_overdue = 1 THEN 1 END) as overdue_count
            ')
            ->groupBy('period_date')
            ->orderBy('period_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'period_type' => $periodType,
                'months' => $months,
            ]
        ]);
    }

    /**
     * Get contract risk analysis
     */
    public function riskAnalysis(Request $request): JsonResponse
    {
        $periodType = $request->get('period_type', 'monthly');

        $riskAnalysis = ContractAnalytics::forPeriod($periodType)
            ->selectRaw('
                CASE 
                    WHEN risk_score >= 80 THEN "Critical"
                    WHEN risk_score >= 60 THEN "High"
                    WHEN risk_score >= 40 THEN "Medium"
                    WHEN risk_score >= 20 THEN "Low"
                    ELSE "Very Low"
                END as risk_level,
                COUNT(*) as contract_count,
                SUM(total_value) as total_value,
                SUM(outstanding_amount) as outstanding_amount
            ')
            ->groupBy('risk_level')
            ->get();

        // Risk factors analysis
        $riskFactors = ContractAnalytics::forPeriod($periodType)
            ->selectRaw('
                AVG(CASE WHEN payment_percentage < 30 THEN 1 ELSE 0 END) * 100 as low_payment_rate,
                AVG(CASE WHEN completion_percentage > 100 THEN 1 ELSE 0 END) * 100 as overrun_rate,
                AVG(CASE WHEN is_overdue = 1 THEN 1 ELSE 0 END) * 100 as overdue_rate,
                AVG(CASE WHEN average_payment_days > 30 THEN 1 ELSE 0 END) * 100 as slow_payment_rate
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'risk_distribution' => $riskAnalysis,
                'risk_factors' => $riskFactors,
            ]
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request): JsonResponse
    {
        $periodType = $request->get('period_type', 'monthly');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subMonths(12);
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();

        $analytics = ContractAnalytics::forPeriod($periodType)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->with('contract')
            ->get();

        $exportData = $analytics->map(function ($analytics) {
            return [
                'contract_number' => $analytics->contract->contract_number,
                'period_date' => $analytics->period_date->format('Y-m-d'),
                'period_type' => $analytics->period_type,
                'total_value' => $analytics->total_value,
                'paid_amount' => $analytics->paid_amount,
                'outstanding_amount' => $analytics->outstanding_amount,
                'payment_percentage' => $analytics->payment_percentage,
                'completion_percentage' => $analytics->completion_percentage,
                'performance_status' => $analytics->performance_status,
                'risk_score' => $analytics->risk_score,
                'is_overdue' => $analytics->is_overdue,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'meta' => [
                'total_records' => $analytics->count(),
                'period_type' => $periodType,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}