<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractStatus extends Model
{
    protected $fillable = ['code', 'name'];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function generalConfigurationNotifications(): HasMany
    {
        return $this->hasMany(GeneralConfigurationNotification::class);
    }
}
