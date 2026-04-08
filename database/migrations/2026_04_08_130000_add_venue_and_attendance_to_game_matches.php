<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_matches', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->after('scheduled_date');
            $table->unsignedInteger('venue_capacity')->nullable()->after('venue_name');
            $table->unsignedInteger('attendance')->nullable()->after('venue_capacity');
            $table->index(['game_id', 'home_team_id', 'played'], 'game_matches_home_team_played_idx');
        });
    }

    public function down(): void
    {
        Schema::table('game_matches', function (Blueprint $table) {
            $table->dropIndex('game_matches_home_team_played_idx');
            $table->dropColumn(['venue_name', 'venue_capacity', 'attendance']);
        });
    }
};
