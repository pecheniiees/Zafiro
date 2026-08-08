<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['club_zone_id', 'number', 'status', 'position_x', 'position_y'])]
class ClubComputer extends Model
{
    protected $attributes = ['status' => 'available', 'position_x' => 5, 'position_y' => 5];

    protected function casts(): array
    {
        return ['number' => 'integer', 'position_x' => 'integer', 'position_y' => 'integer'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ClubZone::class, 'club_zone_id');
    }
}
