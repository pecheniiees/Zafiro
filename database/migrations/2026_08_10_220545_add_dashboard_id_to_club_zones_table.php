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
        Schema::table('club_zones', function (Blueprint $table) {
            $table->foreignId('dashboard_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index(['dashboard_id', 'sort_order']);
        });

        $dashboardId = DB::table('dashboards')->oldest('id')->value('id');

        if ($dashboardId !== null) {
            DB::table('club_zones')->whereNull('dashboard_id')->update(['dashboard_id' => $dashboardId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_zones', function (Blueprint $table) {
            $table->dropIndex(['dashboard_id', 'sort_order']);
            $table->dropConstrainedForeignId('dashboard_id');
        });
    }
};
