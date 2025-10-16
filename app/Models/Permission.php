<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $fillable = ['role_id', 'feature_id', 'enable'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
