<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    protected $fillable = ['category_id', 'title', 'image'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Filter portofolio: kata kunci dan kategori. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['keyword'] ?? null, fn (Builder $q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $v) => $q->where('category_id', $v));
    }
}
