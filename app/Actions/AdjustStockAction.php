<?php

namespace App\Actions;

use App\Models\Dashboard;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustStockAction
{
    public function handle(Dashboard $dashboard, array $data, ?int $userId): StockMovement
    {
        $validatedData = validator($data, [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', 'in:'.StockMovement::TYPE_RECEIPT.','.StockMovement::TYPE_WRITE_OFF],
            'note' => ['nullable', 'string', 'max:255'],
        ])->validate();

        return DB::transaction(function () use ($dashboard, $validatedData, $userId): StockMovement {
            $product = $dashboard->products()
                ->lockForUpdate()
                ->find($validatedData['product_id']);

            if (! $product) {
                throw ValidationException::withMessages(['product_id' => 'Товар не найден в этом dashboard.']);
            }

            $quantity = (int) $validatedData['quantity'];

            if ($validatedData['type'] === StockMovement::TYPE_WRITE_OFF && $quantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Нельзя списать больше, чем есть на складе.',
                ]);
            }

            $product->quantity += $validatedData['type'] === StockMovement::TYPE_RECEIPT
                ? $quantity
                : -$quantity;
            $product->save();

            return $dashboard->stockMovements()->create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'type' => $validatedData['type'],
                'quantity' => $quantity,
                'balance_after' => $product->quantity,
                'note' => $validatedData['note'] ?? null,
            ]);
        });
    }
}
