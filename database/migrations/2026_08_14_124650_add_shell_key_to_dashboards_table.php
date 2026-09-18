<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->string('shell_key', 64)->nullable()->after('slug')->unique();
        });

        DB::table('dashboards')->orderBy('id')->each(function (object $dashboard): void {
            DB::table('dashboards')
                ->where('id', $dashboard->id)
                ->update(['shell_key' => 'club_'.Str::random(40)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropUnique(['shell_key']);
            $table->dropColumn('shell_key');
        });
    }
};
