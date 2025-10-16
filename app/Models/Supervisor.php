<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supervisor extends Model
{
    protected $fillable = [
        'contract_type_id',
        'role_id',
        'user_id'
    ];

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
