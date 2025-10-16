<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RevenueForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_date',
        'forecast_type',
        'forecast_method',
        'predicted_revenue',
        'actual_revenue',
        'confidence_level',
        'accuracy_score',
        'growth_rate',
        'trend_coefficient',
        'trend_direction',
        'seasonal_factor',
        'is_seasonal',
        'active_contracts_count',
        'new_contracts_count',
        'completed_contracts_count',
        'average_contract_value',
        'forecast_factors',
        'notes',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'confidence_level' => 'decimal:2',
        'accuracy_score' => 'decimal:2',
        'growth_rate' => 'decimal:2',
        'trend_coefficient' => 'decimal:6',
        'seasonal_factor' => 'decimal:2',
        'average_contract_value' => 'decimal:2',
        'is_seasonal' => 'boolean',
        'forecast_factors' => 'array',
    ];

    // Scopes
    public function scopeForType($query, $type)
    {
        return $query->where('forecast_type', $type);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('forecast_method', $method);
    }

    public function scopeRecent($query, $months = 12)
    {
        return $query->where('forecast_date', '>=', now()->subMonths($months));
    }

    public function scopeWithActuals($query)
    {
        return $query->whereNotNull('actual_revenue');
    }

    // Accessors
    public function getAccuracyPercentageAttribute()
    {
        if (!$this->actual_revenue || !$this->predicted_revenue) {
            return null;
        }
        
        $accuracy = 100 - (abs($this->actual_revenue - $this->predicted_revenue) / $this->actual_revenue * 100);
        return round($accuracy, 2);
    }

    public function getVarianceAttribute()
    {
        if (!$this->actual_revenue || !$this->predicted_revenue) {
            return null;
        }
        
        return $this->actual_revenue - $this->predicted_revenue;
    }

    public function getVariancePercentageAttribute()
    {
        if (!$this->actual_revenue || !$this->predicted_revenue) {
            return null;
        }
        
        return round(($this->variance / $this->predicted_revenue) * 100, 2);
    }

    public function getConfidenceLevelTextAttribute()
    {
        return match(true) {
            $this->confidence_level >= 90 => 'Very High',
            $this->confidence_level >= 80 => 'High',
            $this->confidence_level >= 70 => 'Medium',
            $this->confidence_level >= 60 => 'Low',
            default => 'Very Low'
        };
    }
}