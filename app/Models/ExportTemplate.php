<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportTemplate extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'contract_type_id',
        'description',
    ];

    public function contractType()
    {
        return $this->belongsTo(ContractType::class);
    }
}
