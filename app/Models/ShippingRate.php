<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingRate extends Model
{
    protected $fillable = ['courier_id', 'destination', 'rate'];

    protected function casts(): array
    {
        return ['rate' => 'integer'];
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
