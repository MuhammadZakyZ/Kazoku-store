<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColor extends Model
{
    protected $fillable = ['product_id', 'name', 'mockup_image', 'mockup_back'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Kolom file mockup untuk sisi tertentu ('depan' atau 'belakang'). */
    public static function columnFor(string $side): string
    {
        return $side === 'belakang' ? 'mockup_back' : 'mockup_image';
    }

    public function mockupPath(string $side): ?string
    {
        return $this->{self::columnFor($side)} ?: null;
    }

    public function hasMockup(string $side = 'depan'): bool
    {
        return filled($this->mockupPath($side));
    }
}
