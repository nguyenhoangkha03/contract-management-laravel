<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['code', 'name', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function generalConfigurationAlertTargets(): HasMany
    {
        return $this->hasMany(GeneralConfigurationAlertTarget::class);
    }

    public function generalConfigurationNotifications(): HasMany
    {
        return $this->hasMany(GeneralConfigurationNotification::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
