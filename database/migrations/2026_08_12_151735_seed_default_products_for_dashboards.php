<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $products = [
            ['sku' => 'DRK-001', 'name' => 'Coca-Cola 0,5 л', 'category' => 'Напитки', 'price' => 650, 'quantity' => 48],
            ['sku' => 'DRK-002', 'name' => 'Red Bull 0,25 л', 'category' => 'Напитки', 'price' => 950, 'quantity' => 32],
            ['sku' => 'DRK-003', 'name' => 'Вода BonAqua 0,5 л', 'category' => 'Напитки', 'price' => 400, 'quantity' => 60],
            ['sku' => 'SNK-001', 'name' => 'Snickers', 'category' => 'Снеки', 'price' => 500, 'quantity' => 40],
            ['sku' => 'SNK-002', 'name' => 'Чипсы Lay\'s', 'category' => 'Снеки', 'price' => 850, 'quantity' => 25],
            ['sku' => 'ACC-001', 'name' => 'Клавиатура Logitech G213', 'category' => 'Аксессуары', 'price' => 32_000, 'quantity' => 8],
            ['sku' => 'ACC-002', 'name' => 'Мышь Logitech G102', 'category' => 'Аксессуары', 'price' => 14_500, 'quantity' => 15],
            ['sku' => 'ACC-003', 'name' => 'Гарнитура HyperX Cloud II', 'category' => 'Аксессуары', 'price' => 48_000, 'quantity' => 6],
            ['sku' => 'ACC-004', 'name' => 'Коврик SteelSeries QcK', 'category' => 'Аксессуары', 'price' => 9_000, 'quantity' => 20],
            ['sku' => 'ACC-005', 'name' => 'Кабель HDMI 2 м', 'category' => 'Аксессуары', 'price' => 3_500, 'quantity' => 12],
        ];

        $now = now();

        DB::table('dashboards')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(100, function ($dashboards) use ($products, $now): void {
                $rows = [];

                foreach ($dashboards as $dashboard) {
                    foreach ($products as $product) {
                        $rows[] = ['dashboard_id' => $dashboard->id] + $product + [
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                DB::table('products')->insertOrIgnore($rows);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally keep products: users may have changed stock, prices, or names after migration.
    }
};
