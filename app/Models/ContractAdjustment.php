<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAdjustment extends Model
{
    protected $fillable = [
        'contract_id',
        'adjusted_field',
        'old_value',
        'new_value',
        'adjusted_by',
        'reason',
        'adjustment_date'
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
