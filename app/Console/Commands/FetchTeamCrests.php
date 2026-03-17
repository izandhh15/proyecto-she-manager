<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchTeamCrests extends Command
{
    protected $signature = 'app:fetch-team-crests
                            {--force : Re-download existing crests}';

    protected $description = 'Download team crests locally from the stored external source image URLs';

    public function handle(): int
    {
        $crestsDir = public_path('crests');

        if (!is_dir($crestsDir)) {
            mkdir($crestsDir, 0755, true);
        }

        $teams = DB::table('teams')
            ->whereNotNull('external_id')
            ->whereNotNull('image')
            ->select('external_id', 'image')
            ->distinct()
            ->get();

        if ($teams->isEmpty()) {
            $this->warn('No teams with external_id and image found. Run app:seed-reference-data first.');

            return self::FAILURE;
        }

        $this->info("Downloading crests for {$teams->count()} teams...");

        $bar = $this->output->createProgressBar($teams->count());
        $bar->start();

        $downloaded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($teams as $team) {
            $id = $team->external_id;
            $filePath = "{$crestsDir}/{$id}.png";

            if (file_exists($filePath) && !$this->option('force')) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $url = $team->image;

            try {
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    file_put_contents($filePath, $response->body());
                    $downloaded++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn("  HTTP {$response->status()} for ID {$id}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->warn("  Failed ID {$id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Done!');
        $this->line("  Downloaded: {$downloaded}");
        $this->line("  Skipped:    {$skipped}");
        $this->line("  Failed:     {$failed}");

        return self::SUCCESS;
    }
}
