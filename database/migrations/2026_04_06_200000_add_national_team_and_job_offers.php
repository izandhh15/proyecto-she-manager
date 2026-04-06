<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->uuid('national_team_id')->nullable()->after('team_id');
            $table->foreign('national_team_id')->references('id')->on('teams')->nullOnDelete();
            $table->boolean('is_sacked')->default(false)->after('pre_season');
        });

        Schema::create('manager_job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('game_id');
            $table->uuid('team_id');
            $table->string('competition_id', 32)->nullable();
            $table->string('offer_type', 16)->default('club'); // club|national
            $table->string('status', 16)->default('pending'); // pending|accepted|declined|expired
            $table->string('season', 16);
            $table->unsignedTinyInteger('target_tier')->nullable();
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('competition_id')->references('id')->on('competitions')->nullOnDelete();

            $table->index(['game_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['offer_type', 'season']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_job_offers');

        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['national_team_id']);
            $table->dropColumn(['national_team_id', 'is_sacked']);
        });
    }
};
