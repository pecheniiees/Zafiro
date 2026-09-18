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
        Schema::table('club_computers', function (Blueprint $table) {
            $table->string('name')->nullable()->after('number');
            $table->string('inventory_number', 80)->nullable()->after('name');
            $table->string('ip_address', 45)->nullable()->after('inventory_number');
            $table->string('specs')->nullable()->after('ip_address');
            $table->text('note')->nullable()->after('specs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_computers', function (Blueprint $table) {
            $table->dropColumn(['name', 'inventory_number', 'ip_address', 'specs', 'note']);
        });
    }
};
