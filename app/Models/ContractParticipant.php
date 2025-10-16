<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractParticipant extends Model
{
    protected $fillable = [
        'contract_id',
        'party_type',
        'company_name',
        'address',
        'tax_code',
        'representative_id',
        'representative_position',
        'phone',
        'bank_account',
        'bank_name'
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
