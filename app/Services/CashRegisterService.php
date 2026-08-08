<?php

namespace App\Services;

use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterService
{
    public function sale(array $items, ?int $userId): CashTransaction
    {
        return DB::transaction(function () use ($items, $userId) {
            $shift = CashShift::query()->where('status', CashShift::STATUS_OPEN)->lockForUpdate()->first();
            if (! $shift) throw ValidationException::withMessages(['items' => 'Сначала откройте смену.']);
            $total = 0.0;
            $receiptItems = [];
            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                if ($quantity > $product->quantity) throw ValidationException::withMessages(['items' => "Недостаточно товара: {$product->name}."]);
                $total += (float) $product->price * $quantity;
                $receiptItems[] = [
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => (float) $product->price,
                    'total' => (float) $product->price * $quantity,
                ];
                $product->decrement('quantity', $quantity);
                StockMovement::create(['product_id'=>$product->id,'user_id'=>$userId,'type'=>StockMovement::TYPE_WRITE_OFF,'quantity'=>$quantity,'balance_after'=>$product->refresh()->quantity,'note'=>'Продажа POS']);
            }
            return $shift->transactions()->create([
                'user_id' => $userId,
                'type' => CashTransaction::TYPE_INCOME,
                'amount' => $total,
                'description' => 'Продажа POS',
                'receipt_data' => ['items' => $receiptItems],
            ]);
        });
    }

    public function open(float $balance, ?int $userId): CashShift
    {
        return DB::transaction(function () use ($balance, $userId) {
            if (CashShift::query()->where('status', CashShift::STATUS_OPEN)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['opening_balance' => 'Смена уже открыта.']);
            }
            return CashShift::create(['opened_by' => $userId, 'status' => CashShift::STATUS_OPEN, 'open_marker' => true, 'opening_balance' => $balance, 'opened_at' => now()]);
        });
    }

    public function transact(array $data, ?int $userId): CashTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $shift = CashShift::query()->where('status', CashShift::STATUS_OPEN)->lockForUpdate()->first();
            if (! $shift) throw ValidationException::withMessages(['amount' => 'Сначала откройте смену.']);
            if ($data['type'] === CashTransaction::TYPE_EXPENSE && (float) $data['amount'] > $this->balance($shift)) throw ValidationException::withMessages(['amount' => 'В кассе недостаточно средств.']);
            return $shift->transactions()->create($data + ['user_id' => $userId]);
        });
    }

    public function close(CashShift $shift, ?int $userId): CashShift
    {
        return DB::transaction(function () use ($shift, $userId) {
            $shift = CashShift::query()->lockForUpdate()->findOrFail($shift->id);
            if ($shift->status !== CashShift::STATUS_OPEN) throw ValidationException::withMessages(['shift' => 'Смена уже закрыта.']);
            $shift->update(['closed_by' => $userId, 'status' => CashShift::STATUS_CLOSED, 'open_marker' => null, 'closing_balance' => $this->balance($shift), 'closed_at' => now()]);
            return $shift;
        });
    }

    public function totals(CashShift $shift): array
    {
        $income = (float) $shift->transactions()->where('type', CashTransaction::TYPE_INCOME)->sum('amount');
        $expense = (float) $shift->transactions()->where('type', CashTransaction::TYPE_EXPENSE)->sum('amount');
        return ['income' => $income, 'expense' => $expense, 'balance' => (float) $shift->opening_balance + $income - $expense];
    }

    private function balance(CashShift $shift): float { return $this->totals($shift)['balance']; }
}
