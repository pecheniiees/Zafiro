<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['dashboard_id', 'opened_by', 'closed_by', 'status', 'open_marker', 'opening_balance', 'closing_balance', 'opened_at', 'closed_at'])]
class CashShift extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected function casts(): array
    {
        return ['opening_balance' => 'decimal:2', 'closing_balance' => 'decimal:2', 'opened_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }
}
