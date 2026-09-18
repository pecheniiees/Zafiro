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
        DB::table('club_computers')->whereIn('status', ['available', 'offline'])->update(['status' => 'off']);
        DB::table('club_computers')->where('status', 'online')->update(['status' => 'on']);
        DB::table('club_computers')->where('status', 'restarting')->update(['status' => 'maintenance']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('club_computers')->where('status', 'off')->update(['status' => 'available']);
        DB::table('club_computers')->where('status', 'on')->update(['status' => 'online']);
        DB::table('club_computers')->where('status', 'maintenance')->update(['status' => 'restarting']);
    }
};
