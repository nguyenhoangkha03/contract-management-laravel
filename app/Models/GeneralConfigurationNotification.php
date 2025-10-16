<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralConfigurationNotification extends Model
{
    protected $fillable = ['role_id', 'status_id', 'enable'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function contractStatus(): BelongsTo
    {
        return $this->belongsTo(ContractStatus::class, 'status_id');
    }
}
