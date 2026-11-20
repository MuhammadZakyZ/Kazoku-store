<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColor extends Model
{
    protected $fillable = ['product_id', 'name', 'mockup_image'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hasMockup(): bool
    {
        return filled($this->mockup_image);
    }
}
