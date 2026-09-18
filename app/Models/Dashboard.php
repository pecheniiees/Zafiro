<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'name', 'slug', 'shell_key', 'plan', 'status', 'settings'])]
class Dashboard extends Model
{
    public const PLAN_STARTER = 'starter';
    public const STATUS_ACTIVE = 'active';

    protected $attributes = [
        'plan' => self::PLAN_STARTER,
        'status' => self::STATUS_ACTIVE,
    ];

    public static function generateShellKey(): string
    {
        do {
            $key = 'club_'.Str::random(40);
        } while (self::query()->where('shell_key', $key)->exists());

        return $key;
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clubZones(): HasMany
    {
        return $this->hasMany(ClubZone::class);
    }

    public function clubMembers(): HasMany
    {
        return $this->hasMany(ClubMember::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }
}
