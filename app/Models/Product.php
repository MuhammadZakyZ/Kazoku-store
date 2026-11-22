<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'base_price', 'image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class);
    }

    public function printAreas(): HasMany
    {
        return $this->hasMany(PrintArea::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter katalog: kata kunci, kategori (id), rentang harga dasar.
     *
     * @param  array{keyword?: ?string, category_id?: ?int, min_price?: ?int, max_price?: ?int}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['keyword'] ?? null, fn (Builder $q, $v) => $q->where(function (Builder $w) use ($v) {
                $w->where('name', 'like', "%{$v}%")->orWhere('description', 'like', "%{$v}%");
            }))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $v) => $q->where('category_id', $v))
            ->when($filters['min_price'] ?? null, fn (Builder $q, $v) => $q->where('base_price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn (Builder $q, $v) => $q->where('base_price', '<=', $v));
    }

    /**
     * BR-10: preview tersedia bila, pada sedikitnya satu sisi, ada warna ber-mockup
     * dan area ber-koordinat pada sisi yang sama.
     */
    public function hasPreview(): bool
    {
        foreach (['depan' => 'mockup_image', 'belakang' => 'mockup_back'] as $side => $column) {
            $hasMockup = $this->colors()->whereNotNull($column)->exists();

            $hasArea = $this->printAreas()
                ->where('side', $side)
                ->whereNotNull('preview_x')
                ->whereNotNull('preview_y')
                ->whereNotNull('preview_w')
                ->whereNotNull('preview_h')
                ->exists();

            if ($hasMockup && $hasArea) {
                return true;
            }
        }

        return false;
    }
}
