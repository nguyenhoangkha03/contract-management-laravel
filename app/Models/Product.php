<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'price', 'number', 'image', 'unit'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function contractProducts(): HasMany
    {
        return $this->hasMany(ContractProduct::class);
    }
}
