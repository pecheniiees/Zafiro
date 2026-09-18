<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dashboard_id', 'user_id', 'balance', 'bonus_balance', 'status'])]
class ClubMember extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_INACTIVE = 'inactive';

    protected $attributes = [
        'balance' => 0,
        'bonus_balance' => 0,
        'status' => self::STATUS_ACTIVE,
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'bonus_balance' => 'decimal:2',
        ];
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
