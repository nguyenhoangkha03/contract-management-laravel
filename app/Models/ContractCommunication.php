<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractCommunication extends Model
{
    protected $fillable = [
        'contract_id',
        'date',
        'person',
        'content',
        'attachments',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'attachments' => 'array',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}