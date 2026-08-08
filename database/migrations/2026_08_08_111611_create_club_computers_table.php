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
        Schema::create('club_computers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_zone_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('status', 20)->default('available')->index();
            $table->unsignedInteger('position_x')->default(5);
            $table->unsignedInteger('position_y')->default(5);
            $table->timestamps();
            $table->unique(['club_zone_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_computers');
    }
};
