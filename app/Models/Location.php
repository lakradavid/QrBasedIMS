<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'code', 'description', 'building', 'floor', 'aisle'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->building, $this->floor, $this->aisle]);
        return implode(' › ', $parts) ?: $this->name;
    }
}
