<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    // Berdiri sendiri (tanpa relasi); dikelola admin.
    protected $fillable = ['customer_name', 'message', 'rating', 'is_published'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /** BR-09: tampil publik hanya bila dipublikasikan. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
