<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractAnalytics;
use App\Models\RevenueForecast;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContractAnalyticsService
{
    public function calculateContractMetrics(Contract $contract, string $periodType = 'monthly'): array
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($contract->start_date);
        $endDate = Carbon::parse($contract->end_date);
        
        // Basic calculations
        $totalValue = $contract->total_value ?? 0;
        $paidAmount = $contract->payments()->sum('amount_paid') ?? 0;
        $outstandingAmount = $totalValue - $paidAmount;
        $paymentPercentage = $totalValue > 0 ? ($paidAmount / $totalValue) * 100 : 0;
        
        // Timeline calculations
        $contractDurationDays = $startDate->diffInDays($endDate);
        $daysSinceStart = $startDate->diffInDays($now);
        $daysToEnd = $now->diffInDays($endDate);
        $completionPercentage = $contractDurationDays > 0 ? ($daysSinceStart / $contractDurationDays) * 100 : 0;
        
        // Performance status
        $performanceStatus = $this->calculatePerformanceStatus($contract, $paymentPercentage, $completionPercentage);
        
        // Overdue calculations
        $isOverdue = $now->gt($endDate) && $outstandingAmount > 0;
        $overdueDays = $isOverdue ? $now->diffInDays($endDate) : 0;
        
        // Revenue calculations
        $monthlyRevenue = $this->calculateMonthlyRevenue($contract, $periodType);
        $projectedRevenue = $this->calculateProjectedRevenue($contract, $totalValue, $completionPercentage);
        
        // Risk score
        $riskScore = $this->calculateRiskScore($contract, $paymentPercentage, $completionPercentage, $isOverdue);
        
        // Client metrics
        $invoiceCount = $contract->invoices()->count();
        $paymentCount = $contract->payments()->count();
        $averagePaymentDays = $this->calculateAveragePaymentDays($contract);
        
        return [
            'total_value' => $totalValue,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
            'payment_percentage' => round($paymentPercentage, 2),
            'contract_duration_days' => $contractDurationDays,
            'days_since_start' => $daysSinceStart,
            'days_to_end' => $daysToEnd,
            'completion_percentage' => round($completionPercentage, 2),
            'performance_status' => $performanceStatus,
            'is_overdue' => $isOverdue,
            'overdue_days' => $overdueDays,
            'monthly_revenue' => $monthlyRevenue,
            'projected_revenue' => $projectedRevenue,
            'risk_score' => round($riskScore, 2),
            'invoice_count' => $invoiceCount,
            'payment_count' => $paymentCount,
            'average_payment_days' => round($averagePaymentDays, 2),
        ];
    }
    
    public function updateContractAnalytics(Contract $contract, string $periodType = 'monthly'): ContractAnalytics
    {
        $metrics = $this->calculateContractMetrics($contract, $periodType);
        $periodDate = $this->getPeriodDate($periodType);
        
        return ContractAnalytics::updateOrCreate(
            [
                'contract_id' => $contract->id,
                'period_date' => $periodDate,
                'period_type' => $periodType,
            ],
            array_merge($metrics, [
                'period_date' => $periodDate,
                'period_type' => $periodType,
            ])
        );
    }
    
    public function generatePerformanceReport(string $periodType = 'monthly', ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subMonths(12);
        $endDate = $endDate ?? Carbon::now();
        
        $analytics = ContractAnalytics::forPeriod($periodType)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->with('contract')
            ->get();
        
        // If no analytics data, return default values
        if ($analytics->isEmpty()) {
            return [
                'total_contracts' => 0,
                'total_value' => 0,
                'total_paid' => 0,
                'total_outstanding' => 0,
                'average_payment_percentage' => 0,
                'overdue_contracts' => 0,
                'high_risk_contracts' => 0,
                'performance_breakdown' => [],
                'monthly_trend' => [],
            ];
        }
        
        return [
            'total_contracts' => $analytics->count(),
            'total_value' => $analytics->sum('total_value'),
            'total_paid' => $analytics->sum('paid_amount'),
            'total_outstanding' => $analytics->sum('outstanding_amount'),
            'average_payment_percentage' => $analytics->avg('payment_percentage') ?? 0,
            'overdue_contracts' => $analytics->where('is_overdue', true)->count(),
            'high_risk_contracts' => $analytics->where('risk_score', '>=', 70)->count(),
            'performance_breakdown' => $analytics->groupBy('performance_status')->map->count()->toArray(),
            'monthly_trend' => $this->calculateMonthlyTrend($analytics),
        ];
    }
    
    public function generateRevenueForecast(string $forecastType = 'monthly', int $periodsAhead = 12): array
    {
        $historicalData = $this->getHistoricalRevenue($forecastType, 24);
        $forecasts = [];
        
        for ($i = 1; $i <= $periodsAhead; $i++) {
            $forecastDate = $this->getNextPeriodDate($forecastType, $i);
            $forecast = $this->calculateLinearForecast($historicalData, $i);
            
            $forecasts[] = [
                'forecast_date' => $forecastDate,
                'forecast_type' => $forecastType,
                'forecast_method' => 'linear',
                'predicted_revenue' => $forecast['predicted_revenue'],
                'confidence_level' => $forecast['confidence_level'],
                'growth_rate' => $forecast['growth_rate'],
                'trend_coefficient' => $forecast['trend_coefficient'],
                'trend_direction' => $forecast['trend_direction'],
                'active_contracts_count' => $this->getActiveContractsCount($forecastDate),
                'average_contract_value' => $this->getAverageContractValue(),
            ];
        }
        
        return $forecasts;
    }
    
    public function saveRevenueForecast(array $forecastData): RevenueForecast
    {
        return RevenueForecast::create($forecastData);
    }
    
    private function calculatePerformanceStatus(Contract $contract, float $paymentPercentage, float $completionPercentage): string
    {
        $paymentRatio = $paymentPercentage / 100;
        $completionRatio = $completionPercentage / 100;
        
        if ($paymentRatio >= 0.9 && $completionRatio <= 1.1) {
            return 'excellent';
        } elseif ($paymentRatio >= 0.7 && $completionRatio <= 1.2) {
            return 'good';
        } elseif ($paymentRatio >= 0.5 && $completionRatio <= 1.5) {
            return 'normal';
        } elseif ($paymentRatio >= 0.3) {
            return 'poor';
        } else {
            return 'critical';
        }
    }
    
    private function calculateMonthlyRevenue(Contract $contract, string $periodType): float
    {
        $period = $periodType === 'monthly' ? 1 : ($periodType === 'quarterly' ? 3 : 12);
        $startDate = Carbon::now()->subMonths($period);
        
        return $contract->payments()
            ->where('payment_date', '>=', $startDate)
            ->sum('amount_paid') ?? 0;
    }
    
    private function calculateProjectedRevenue(Contract $contract, float $totalValue, float $completionPercentage): float
    {
        if ($completionPercentage >= 100) {
            return $totalValue;
        }
        
        $remainingPercentage = 100 - $completionPercentage;
        $projectedCompletion = $completionPercentage + ($remainingPercentage * 0.8); // Assume 80% completion rate
        
        return ($totalValue * $projectedCompletion) / 100;
    }
    
    private function calculateRiskScore(Contract $contract, float $paymentPercentage, float $completionPercentage, bool $isOverdue): float
    {
        $riskScore = 0;
        
        // Payment risk (40% weight)
        if ($paymentPercentage < 30) {
            $riskScore += 40;
        } elseif ($paymentPercentage < 60) {
            $riskScore += 25;
        } elseif ($paymentPercentage < 80) {
            $riskScore += 15;
        }
        
        // Timeline risk (30% weight)
        if ($completionPercentage > 120) {
            $riskScore += 30;
        } elseif ($completionPercentage > 100) {
            $riskScore += 20;
        } elseif ($completionPercentage > 90) {
            $riskScore += 10;
        }
        
        // Overdue risk (20% weight)
        if ($isOverdue) {
            $riskScore += 20;
        }
        
        // Client payment history (10% weight)
        $averagePaymentDays = $this->calculateAveragePaymentDays($contract);
        if ($averagePaymentDays > 45) {
            $riskScore += 10;
        } elseif ($averagePaymentDays > 30) {
            $riskScore += 5;
        }
        
        return min($riskScore, 100);
    }
    
    private function calculateAveragePaymentDays(Contract $contract): float
    {
        $invoices = $contract->invoices()->with('contract.payments')->get();
        $totalDays = 0;
        $count = 0;
        
        foreach ($invoices as $invoice) {
            $payment = $contract->payments()
                ->where('payment_date', '>=', $invoice->issue_date)
                ->first();
            
            if ($payment) {
                $days = Carbon::parse($invoice->issue_date)->diffInDays(Carbon::parse($payment->payment_date));
                $totalDays += $days;
                $count++;
            }
        }
        
        return $count > 0 ? $totalDays / $count : 0;
    }
    
    private function getPeriodDate(string $periodType): Carbon
    {
        $now = Carbon::now();
        
        return match($periodType) {
            'monthly' => $now->startOfMonth(),
            'quarterly' => $now->startOfQuarter(),
            'yearly' => $now->startOfYear(),
            default => $now->startOfMonth(),
        };
    }
    
    private function getNextPeriodDate(string $periodType, int $periodsAhead): Carbon
    {
        $now = Carbon::now();
        
        return match($periodType) {
            'monthly' => $now->addMonths($periodsAhead)->startOfMonth(),
            'quarterly' => $now->addQuarters($periodsAhead)->startOfQuarter(),
            'yearly' => $now->addYears($periodsAhead)->startOfYear(),
            default => $now->addMonths($periodsAhead)->startOfMonth(),
        };
    }
    
    private function getHistoricalRevenue(string $periodType, int $periods): Collection
    {
        $startDate = Carbon::now()->subMonths($periods);
        
        return ContractAnalytics::forPeriod($periodType)
            ->where('period_date', '>=', $startDate)
            ->orderBy('period_date')
            ->get()
            ->groupBy('period_date')
            ->map(function ($group) {
                return [
                    'period_date' => $group->first()->period_date,
                    'revenue' => $group->sum('monthly_revenue'),
                ];
            });
    }
    
    private function calculateLinearForecast(Collection $historicalData, int $periodsAhead): array
    {
        $revenues = $historicalData->pluck('revenue')->values()->toArray();
        $count = count($revenues);
        
        if ($count < 2) {
            return [
                'predicted_revenue' => 0,
                'confidence_level' => 0,
                'growth_rate' => 0,
                'trend_coefficient' => 0,
                'trend_direction' => 'stable',
            ];
        }
        
        // Calculate linear trend
        $x = range(1, $count);
        $y = $revenues;
        
        $n = $count;
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $y));
        $sumX2 = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Predict future value
        $predictedRevenue = $intercept + $slope * ($count + $periodsAhead);
        
        // Calculate confidence level based on R-squared
        $yMean = array_sum($y) / $n;
        $ssRes = array_sum(array_map(function($yi, $xi) use ($slope, $intercept) {
            $predicted = $intercept + $slope * $xi;
            return pow($yi - $predicted, 2);
        }, $y, $x));
        $ssTot = array_sum(array_map(function($yi) use ($yMean) {
            return pow($yi - $yMean, 2);
        }, $y));
        
        $rSquared = 1 - ($ssRes / $ssTot);
        $confidenceLevel = max(0, min(100, $rSquared * 100));
        
        // Calculate growth rate
        $growthRate = $count > 1 ? (($revenues[$count - 1] - $revenues[0]) / $revenues[0]) * 100 : 0;
        
        // Determine trend direction
        $trendDirection = $slope > 0.01 ? 'increasing' : ($slope < -0.01 ? 'decreasing' : 'stable');
        
        return [
            'predicted_revenue' => max(0, $predictedRevenue),
            'confidence_level' => $confidenceLevel,
            'growth_rate' => $growthRate,
            'trend_coefficient' => $slope,
            'trend_direction' => $trendDirection,
        ];
    }
    
    private function calculateMonthlyTrend(Collection $analytics): array
    {
        return $analytics->groupBy(function ($item) {
            return Carbon::parse($item->period_date)->format('Y-m');
        })->map(function ($group) {
            return [
                'period' => $group->first()->period_date->format('Y-m'),
                'total_value' => $group->sum('total_value'),
                'paid_amount' => $group->sum('paid_amount'),
                'contract_count' => $group->count(),
            ];
        })->values()->toArray();
    }
    
    private function getActiveContractsCount(Carbon $date): int
    {
        return Contract::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->count();
    }
    
    private function getAverageContractValue(): float
    {
        return Contract::avg('total_value') ?? 0;
    }
}