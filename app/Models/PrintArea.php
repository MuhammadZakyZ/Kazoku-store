<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintArea extends Model
{
    protected $fillable = [
        'product_id', 'name', 'width_cm', 'height_cm', 'price',
        'preview_x', 'preview_y', 'preview_w', 'preview_h',
    ];

    protected function casts(): array
    {
        return [
            'width_cm' => 'float',
            'height_cm' => 'float',
            'price' => 'integer',
            'preview_x' => 'float',
            'preview_y' => 'float',
            'preview_w' => 'float',
            'preview_h' => 'float',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    /** Contoh: "10×10 cm" */
    protected function sizeLabel(): Attribute
    {
        return Attribute::get(fn () => rtrim(rtrim(number_format($this->width_cm, 1, '.', ''), '0'), '.')
            .'×'
            .rtrim(rtrim(number_format($this->height_cm, 1, '.', ''), '0'), '.')
            .' cm');
    }

    /** Contoh: "Dada kiri (10×10 cm)" */
    protected function label(): Attribute
    {
        return Attribute::get(fn () => "{$this->name} ({$this->size_label})");
    }

    public function hasPreviewCoordinates(): bool
    {
        return $this->preview_x !== null && $this->preview_y !== null
            && $this->preview_w !== null && $this->preview_h !== null;
    }
}
