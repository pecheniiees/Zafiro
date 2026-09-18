<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['dashboard_id', 'cash_shift_id', 'user_id', 'type', 'amount', 'description', 'receipt_data'])]
class CashTransaction extends Model
{
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'receipt_data' => 'array'];
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }
}
