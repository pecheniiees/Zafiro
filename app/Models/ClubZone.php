<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['dashboard_id', 'name', 'description', 'capacity', 'icon', 'theme', 'status', 'hourly_price', 'is_featured', 'sort_order', 'position_x', 'position_y', 'width', 'height'])]
class ClubZone extends Model
{
    protected $attributes = ['icon' => 'desktop', 'theme' => 'neutral', 'status' => 'available', 'hourly_price' => null, 'is_featured' => false, 'sort_order' => 0, 'position_x' => 20, 'position_y' => 20, 'width' => 320, 'height' => 180];

    protected function casts(): array
    {
        return ['capacity' => 'integer', 'hourly_price' => 'decimal:2', 'is_featured' => 'boolean', 'sort_order' => 'integer', 'position_x' => 'integer', 'position_y' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function computers(): HasMany
    {
        return $this->hasMany(ClubComputer::class)->orderBy('number');
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }
}
