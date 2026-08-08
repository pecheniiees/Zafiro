<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('club_zones', function (Blueprint $table) {
            $table->unsignedInteger('position_x')->default(20)->after('sort_order');
            $table->unsignedInteger('position_y')->default(20)->after('position_x');
            $table->unsignedInteger('width')->default(320)->after('position_y');
            $table->unsignedInteger('height')->default(180)->after('width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_zones', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'width', 'height']);
        });
    }
};
