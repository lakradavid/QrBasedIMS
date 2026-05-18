<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'type',
        'quantity', 'quantity_before', 'quantity_after',
        'from_location_id', 'to_location_id',
        'reference', 'notes',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in'         => 'Stock In',
            'out'        => 'Stock Out',
            'adjustment' => 'Adjustment',
            'transfer'   => 'Transfer',
            default      => ucfirst($this->type),
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'in'         => 'success',
            'out'        => 'danger',
            'adjustment' => 'warning',
            'transfer'   => 'info',
            default      => 'secondary',
        };
    }
}
