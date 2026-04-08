<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_players', function (Blueprint $table) {
            $table->uuid('source_game_player_id')->nullable()->after('team_id');
            $table->uuid('source_team_id')->nullable()->after('source_game_player_id');
            $table->date('contract_until')->nullable()->after('appeared_at');

            $table->index(
                ['game_id', 'team_id', 'source_game_player_id'],
                'academy_players_game_team_source_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('academy_players', function (Blueprint $table) {
            $table->dropIndex('academy_players_game_team_source_idx');
            $table->dropColumn(['source_game_player_id', 'source_team_id', 'contract_until']);
        });
    }
};
