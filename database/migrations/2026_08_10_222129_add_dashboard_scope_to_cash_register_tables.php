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
        Schema::table('cash_shifts', function (Blueprint $table) {
            $table->foreignId('dashboard_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique(['open_marker']);
            $table->unique(['dashboard_id', 'open_marker']);
            $table->index(['dashboard_id', 'status']);
        });

        $dashboardId = DB::table('dashboards')->oldest('id')->value('id');

        if ($dashboardId !== null) {
            DB::table('cash_shifts')->whereNull('dashboard_id')->update(['dashboard_id' => $dashboardId]);
        }

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('dashboard_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index(['dashboard_id', 'created_at']);
        });

        DB::table('cash_transactions')
            ->join('cash_shifts', 'cash_transactions.cash_shift_id', '=', 'cash_shifts.id')
            ->whereNull('cash_transactions.dashboard_id')
            ->select(['cash_transactions.id', 'cash_shifts.dashboard_id'])
            ->orderBy('cash_transactions.id')
            ->get()
            ->each(function (object $transaction): void {
                DB::table('cash_transactions')
                    ->where('id', $transaction->id)
                    ->update(['dashboard_id' => $transaction->dashboard_id]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropIndex(['dashboard_id', 'created_at']);
            $table->dropConstrainedForeignId('dashboard_id');
        });

        Schema::table('cash_shifts', function (Blueprint $table) {
            $table->dropIndex(['dashboard_id', 'status']);
            $table->dropUnique(['dashboard_id', 'open_marker']);
            $table->unique('open_marker');
            $table->dropConstrainedForeignId('dashboard_id');
        });
    }
};
