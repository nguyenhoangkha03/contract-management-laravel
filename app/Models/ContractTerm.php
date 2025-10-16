<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTerm extends Model
{
    protected $fillable = [
        'contract_id',
        'term_type',
        'conent',
        'order',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
