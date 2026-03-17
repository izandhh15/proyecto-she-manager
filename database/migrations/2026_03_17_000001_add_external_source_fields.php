<?php

use App\Support\ExternalData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('external_source')->nullable()->after('id');
            $table->string('external_id')->nullable()->after('external_source');
            $table->index(['external_source', 'external_id']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('external_source')->nullable()->after('id');
            $table->string('external_id')->nullable()->after('external_source');
            $table->index(['external_source', 'external_id']);
        });

        DB::table('teams')
            ->whereNotNull('transfermarkt_id')
            ->update([
                'external_source' => ExternalData::SOURCE_TRANSFERMARKT,
                'external_id' => DB::raw("CAST(transfermarkt_id AS CHAR)"),
            ]);

        DB::table('players')
            ->whereNotNull('transfermarkt_id')
            ->update([
                'external_source' => ExternalData::SOURCE_TRANSFERMARKT,
                'external_id' => DB::raw("CAST(transfermarkt_id AS CHAR)"),
            ]);
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex(['external_source', 'external_id']);
            $table->dropColumn(['external_source', 'external_id']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['external_source', 'external_id']);
            $table->dropColumn(['external_source', 'external_id']);
        });
    }
};
