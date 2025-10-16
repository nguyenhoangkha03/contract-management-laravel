<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    // protected $fillable = ['patient_id', 'treatment_type', 'treatment_date', 'notes'];
    protected $casts = [
        'price' => MoneyCast::class,
    ];
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
