<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('dashboard_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['sku']);
            $table->unique(['dashboard_id', 'sku']);
            $table->index(['dashboard_id', 'name']);
        });

        $dashboardId = DB::table('dashboards')->oldest('id')->value('id');

        if ($dashboardId !== null) {
            DB::table('products')->whereNull('dashboard_id')->update(['dashboard_id' => $dashboardId]);
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('dashboard_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index(['dashboard_id', 'created_at']);
        });

        DB::table('stock_movements')
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->whereNull('stock_movements.dashboard_id')
            ->select(['stock_movements.id', 'products.dashboard_id'])
            ->orderBy('stock_movements.id')
            ->get()
            ->each(function (object $movement): void {
                DB::table('stock_movements')
                    ->where('id', $movement->id)
                    ->update(['dashboard_id' => $movement->dashboard_id]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['dashboard_id', 'created_at']);
            $table->dropConstrainedForeignId('dashboard_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['dashboard_id', 'name']);
            $table->dropUnique(['dashboard_id', 'sku']);
            $table->unique('sku');
            $table->dropConstrainedForeignId('dashboard_id');
        });
    }
};
