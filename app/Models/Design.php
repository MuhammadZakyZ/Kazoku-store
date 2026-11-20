<?php

namespace App\Models;

use App\Enums\DesignFileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Design extends Model
{
    // Diisi oleh server (snapshot area + path file), bukan dari form.
    protected $fillable = [
        'order_id', 'print_area_id', 'area_name', 'print_size', 'area_price', 'file', 'type',
    ];

    protected function casts(): array
    {
        return [
            'area_price' => 'integer',
            'type' => DesignFileType::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function printArea(): BelongsTo
    {
        return $this->belongsTo(PrintArea::class);
    }
}
