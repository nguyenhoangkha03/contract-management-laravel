<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContractAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'period_date',
        'period_type',
        'total_value',
        'paid_amount',
        'outstanding_amount',
        'payment_percentage',
        'contract_duration_days',
        'days_since_start',
        'days_to_end',
        'completion_percentage',
        'performance_status',
        'is_overdue',
        'overdue_days',
        'monthly_revenue',
        'projected_revenue',
        'risk_score',
        'invoice_count',
        'payment_count',
        'average_payment_days',
    ];

    protected $casts = [
        'period_date' => 'date',
        'total_value' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'payment_percentage' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'monthly_revenue' => 'decimal:2',
        'projected_revenue' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'average_payment_days' => 'decimal:2',
        'is_overdue' => 'boolean',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    // Scopes
    public function scopeForPeriod($query, $type = 'monthly')
    {
        return $query->where('period_type', $type);
    }

    public function scopeForContract($query, $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    public function scopeByPerformance($query, $status)
    {
        return $query->where('performance_status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true);
    }

    public function scopeHighRisk($query, $threshold = 70)
    {
        return $query->where('risk_score', '>=', $threshold);
    }

    // Accessors
    public function getPerformanceColorAttribute()
    {
        return match($this->performance_status) {
            'excellent' => 'success',
            'good' => 'info',
            'normal' => 'warning',
            'poor' => 'danger',
            'critical' => 'danger',
            default => 'secondary'
        };
    }

    public function getRiskLevelAttribute()
    {
        return match(true) {
            $this->risk_score >= 80 => 'Critical',
            $this->risk_score >= 60 => 'High',
            $this->risk_score >= 40 => 'Medium',
            $this->risk_score >= 20 => 'Low',
            default => 'Very Low'
        };
    }
}