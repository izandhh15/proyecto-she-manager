<?php

namespace App\Console\Commands;

use App\Support\ExternalData;
use App\Support\SoccerdonnaPlayerOverrides;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportSoccerdonnaRosters extends Command
{
    protected $signature = 'app:import-soccerdonna-rosters
                            {competition?* : Competition codes to import (default: all supported domestic leagues)}
                            {--season=2025 : Season folder to update}
                            {--dry-run : Fetch and parse without writing files}
                            {--club-limit= : Limit imported clubs per competition for testing}
                            {--sleep-ms=150 : Delay between HTTP requests in milliseconds}';

    protected $description = 'Refresh 2025/26 domestic league squads from Soccerdonna';

    /**
     * Only the real domestic competitions can be synced from Soccerdonna.
     * The repo UCL/UEL/UECL datasets are fictionalized and intentionally excluded.
     *
     * @var array<string, string>
     */
    private const COMPETITION_URLS = [
        'ESP1' => 'https://www.soccerdonna.de/en/primera-division-femenina/startseite/wettbewerb_ESP1.html',
        'ESP2' => 'https://www.soccerdonna.de/en/primera-federacion/startseite/wettbewerb_ESPR.html',
        'ENG1' => 'https://www.soccerdonna.de/en/womens-super-league/startseite/wettbewerb_ENG1.html',
        'DEU1' => 'https://www.soccerdonna.de/en/1-bundesliga/startseite/wettbewerb_BL1.html',
        'FRA1' => 'https://www.soccerdonna.de/en/premire-ligue/startseite/wettbewerb_DAN1.html',
        'ITA1' => 'https://www.soccerdonna.de/en/serie-a-femminile/startseite/wettbewerb_IT1.html',
    ];

    private const POSITION_MARKET_WEIGHTS = [
        'Goalkeeper' => 0.85,
        'Centre-Back' => 1.00,
        'Left-Back' => 0.95,
        'Right-Back' => 0.95,
        'Defensive Midfield' => 1.00,
        'Central Midfield' => 1.00,
        'Attacking Midfield' => 1.10,
        'Left Midfield' => 0.95,
        'Right Midfield' => 0.95,
        'Left Winger' => 1.05,
        'Right Winger' => 1.05,
        'Centre-Forward' => 1.15,
        'Second Striker' => 1.10,
    ];

    private int $sleepMs = 150;

    private string $season = '2025';

    /** @var array<string, array<string, mixed>> */
    private array $playerProfileCache = [];

    public function handle(): int
    {
        $requestedCompetitions = $this->argument('competition');
        $competitions = empty($requestedCompetitions)
            ? array_keys(self::COMPETITION_URLS)
            : array_map('strtoupper', $requestedCompetitions);

        $unsupported = array_values(array_diff($competitions, array_keys(self::COMPETITION_URLS)));
        if (! empty($unsupported)) {
            $this->error('Unsupported competition(s): ' . implode(', ', $unsupported));
            $this->line('Supported: ' . implode(', ', array_keys(self::COMPETITION_URLS)));

            return CommandAlias::FAILURE;
        }

        $this->sleepMs = max(0, (int) $this->option('sleep-ms'));
        $season = (string) $this->option('season');
        $this->season = $season;
        $clubLimit = $this->option('club-limit');
        $clubLimit = $clubLimit !== null ? max(1, (int) $clubLimit) : null;
        $dryRun = (bool) $this->option('dry-run');

        foreach ($competitions as $competition) {
            try {
                $this->importCompetition($competition, $season, $clubLimit, $dryRun);
            } catch (\Throwable $e) {
                $this->error("FAILED {$competition}: {$e->getMessage()}");
                $this->error("  at {$e->getFile()}:{$e->getLine()}");

                return CommandAlias::FAILURE;
            }
        }

        return CommandAlias::SUCCESS;
    }

    private function importCompetition(string $competition, string $season, ?int $clubLimit, bool $dryRun): void
    {
        $path = base_path("data/{$season}/{$competition}/teams.json");
        if (! file_exists($path)) {
            throw new \RuntimeException("Competition file not found: {$path}");
        }

        $existing = ExternalData::decodeJsonFile($path);
        $existingByKey = collect($existing['clubs'] ?? [])
            ->keyBy(fn (array $club) => $this->normalizeName($club['name'] ?? ''));

        $this->newLine();
        $this->info("Importing {$competition} from Soccerdonna...");

        $clubRefs = $this->fetchCompetitionClubRefs(self::COMPETITION_URLS[$competition]);
        if ($clubLimit !== null) {
            $clubRefs = array_slice($clubRefs, 0, $clubLimit);
        }

        $clubs = [];
        foreach ($clubRefs as $index => $clubRef) {
            $this->line(sprintf('  [%d/%d] %s', $index + 1, count($clubRefs), $clubRef['name']));
            $players = $this->fetchClubPlayers($clubRef['squad_url']);
            $existingClub = $existingByKey->get($this->normalizeName($clubRef['name']));

            $clubs[] = [
                'id' => $clubRef['id'],
                'externalId' => $clubRef['id'],
                'transfermarktId' => $clubRef['id'],
                'name' => $clubRef['name'],
                'image' => $clubRef['image'] ?? ($existingClub['image'] ?? null),
                'stadiumName' => $existingClub['stadiumName'] ?? null,
                'stadiumSeats' => $existingClub['stadiumSeats'] ?? null,
                'players' => $players,
                '_market_value_total_eur' => $clubRef['market_value_total_eur'],
                '_market_value_average_eur' => $clubRef['market_value_average_eur'],
            ];
        }

        $clubs = $this->fillMissingPlayerData($clubs);
        $clubs = array_map(function (array $club): array {
            unset($club['_market_value_total_eur'], $club['_market_value_average_eur']);

            return $club;
        }, $clubs);

        $payload = $existing;
        $payload['clubs'] = $clubs;
        $payload['seasonID'] = $season;

        if (! $dryRun) {
            file_put_contents(
                $path,
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL
            );
        }

        $playerCount = array_sum(array_map(fn (array $club) => count($club['players']), $clubs));
        $suffix = $dryRun ? ' (dry-run)' : '';
        $this->info("Completed {$competition}: " . count($clubs) . " clubs, {$playerCount} players{$suffix}");
    }

    /**
     * @return array<int, array{id: string, name: string, image: ?string, squad_url: string, market_value_total_eur: ?int, market_value_average_eur: ?int}>
     */
    private function fetchCompetitionClubRefs(string $url): array
    {
        $xpath = $this->loadXPath($this->fetchHtml($url));
        $heading = $xpath->query("//p[contains(normalize-space(.), 'Teams of ')]")->item(0);

        if (! $heading) {
            throw new \RuntimeException("Could not find teams heading on competition page: {$url}");
        }

        $table = $this->firstFollowingElement($heading, 'table');
        if (! $table) {
            throw new \RuntimeException("Could not find teams table on competition page: {$url}");
        }

        $rows = $xpath->query(".//tr[td[a[contains(@href, '/startseite/verein_')]]]", $table);
        $clubs = [];

        foreach ($rows as $row) {
            $nameLink = $xpath->query(".//td[2]//a[contains(@href, '/startseite/verein_')][1]", $row)->item(0);
            if (! $nameLink) {
                continue;
            }

            $href = $nameLink->attributes?->getNamedItem('href')?->nodeValue;
            if (! $href || ! preg_match('/verein_(\d+)\.html$/', $href, $matches)) {
                continue;
            }

            $crest = $xpath->query(".//img[contains(@src, '/wappen/')][1]", $row)->item(0);
            $cells = $xpath->query('./td', $row);

            $clubs[] = [
                'id' => $matches[1],
                'name' => $this->cleanText($nameLink->textContent),
                'image' => $crest?->attributes?->getNamedItem('src')?->nodeValue
                    ? $this->absoluteUrl($crest->attributes->getNamedItem('src')->nodeValue)
                    : null,
                'squad_url' => $this->absoluteUrl(str_replace('/startseite/', '/kader/', $href)),
                'market_value_total_eur' => $cells->length >= 5 ? $this->parseEuroAmount($cells->item(4)->textContent) : null,
                'market_value_average_eur' => $cells->length >= 6 ? $this->parseEuroAmount($cells->item(5)->textContent) : null,
            ];
        }

        if (empty($clubs)) {
            throw new \RuntimeException("No club links parsed from competition page: {$url}");
        }

        return $clubs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchClubPlayers(string $url): array
    {
        $xpath = $this->loadXPath($this->fetchHtml($url));
        $table = $xpath->query("//table[@id='spieler']")->item(0);

        if (! $table) {
            throw new \RuntimeException("Could not find squad table on club page: {$url}");
        }

        $rows = $xpath->query(".//tbody/tr", $table);
        $players = [];

        foreach ($rows as $row) {
            $profileLink = $xpath->query(".//a[contains(@href, '/profil/spieler_')][1]", $row)->item(0);
            if (! $profileLink) {
                continue;
            }

            $profileHref = $profileLink->attributes?->getNamedItem('href')?->nodeValue;
            if (! $profileHref || ! preg_match('/spieler_(\d+)\.html$/', $profileHref, $matches)) {
                continue;
            }

            $cells = $xpath->query('./td', $row);
            if ($cells->length < 8) {
                continue;
            }

            $profile = $this->fetchPlayerProfile($this->absoluteUrl($profileHref));

            $player = [
                'id' => $matches[1],
                'externalId' => $matches[1],
                'name' => $profile['name'] ?? $this->cleanText($profileLink->textContent),
                'position' => $this->mapPosition(
                    $profile['position'] ?? null,
                    $this->cleanText($xpath->query(".//td[2]//tr[2]/td[1]", $row)->item(0)?->textContent ?? '')
                ),
                'number' => $this->normalizeNumber($cells->item(0)->textContent),
                'age' => $this->normalizeAge($cells->item(3)->textContent),
                'nationality' => $profile['nationality'] ?: $this->extractFlagTitles($xpath, $cells->item(5)),
                'dateOfBirth' => $profile['date_of_birth'],
                'foot' => $this->normalizeFoot($profile['foot'] ?? $cells->item(2)->textContent),
                'height' => $this->normalizeHeight($profile['height'] ?? $cells->item(4)->textContent),
                'marketValue' => $profile['market_value'],
                'contract' => $profile['contract_until'] ?? $this->normalizeContractDate($cells->item(7)->textContent),
            ];

            $players[] = SoccerdonnaPlayerOverrides::apply($this->season, $player);
        }

        return $players;
    }

    /**
     * @return array{name: string, date_of_birth: ?string, nationality: array<int, string>, position: ?string, foot: ?string, height: ?string, market_value: ?string, contract_until: ?string}
     */
    private function fetchPlayerProfile(string $url): array
    {
        if (isset($this->playerProfileCache[$url])) {
            return $this->playerProfileCache[$url];
        }

        $xpath = $this->loadXPath($this->fetchHtml($url));
        $table = $xpath->query("//p[contains(normalize-space(.), 'The profile for')]/following-sibling::table[1]")->item(0);

        if (! $table) {
            throw new \RuntimeException("Could not find player profile table: {$url}");
        }

        $data = [
            'name' => $this->cleanText($xpath->query("//td[contains(@class, 'blau')]//h1[1]")->item(0)?->textContent ?? ''),
            'date_of_birth' => null,
            'nationality' => [],
            'position' => null,
            'foot' => null,
            'height' => null,
            'market_value' => null,
            'contract_until' => null,
        ];

        foreach ($xpath->query('.//tr', $table) as $row) {
            $cells = $xpath->query('./td', $row);
            if ($cells->length < 2) {
                continue;
            }

            $label = Str::lower(rtrim($this->cleanText($cells->item(0)->textContent), ':'));
            $value = $this->cleanText($cells->item(1)->textContent);

            match ($label) {
                'date of birth' => $data['date_of_birth'] = $this->normalizeDate($value),
                'height' => $data['height'] = $this->normalizeHeight($value),
                'nationality' => $data['nationality'] = $this->extractFlagTitles($xpath, $cells->item(1)),
                'position' => $data['position'] = $value,
                'foot' => $data['foot'] = $this->normalizeFoot($value),
                'market value' => $data['market_value'] = $this->normalizeMarketValue($value),
                'contract until' => $data['contract_until'] = $this->normalizeContractDate($value),
                default => null,
            };
        }

        $data['name'] = preg_replace('/^\d+\s+/', '', $data['name']) ?: $data['name'];

        return $this->playerProfileCache[$url] = $data;
    }

    private function fetchHtml(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])
            ->withoutVerifying()
            ->timeout(30)
            ->retry(3, 400)
            ->get($url);

        $response->throw();

        if ($this->sleepMs > 0) {
            usleep($this->sleepMs * 1000);
        }

        return $response->body();
    }

    private function loadXPath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        return new \DOMXPath($dom);
    }

    private function firstFollowingElement(\DOMNode $node, string $tagName): ?\DOMElement
    {
        $next = $node->nextSibling;

        while ($next) {
            if ($next instanceof \DOMElement && Str::lower($next->tagName) === Str::lower($tagName)) {
                return $next;
            }

            $next = $next->nextSibling;
        }

        return null;
    }

    private function absoluteUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        return str_starts_with($url, 'http')
            ? $url
            : 'https://www.soccerdonna.de' . $url;
    }

    /**
     * @return array<int, string>
     */
    private function extractFlagTitles(\DOMXPath $xpath, ?\DOMNode $context): array
    {
        if (! $context) {
            return [];
        }

        $titles = [];
        foreach ($xpath->query(".//img[@title]", $context) as $image) {
            $title = $this->cleanText($image->attributes?->getNamedItem('title')?->nodeValue ?? '');
            if ($title !== '' && $title !== '-') {
                $titles[] = $title;
            }
        }

        return array_values(array_unique($titles));
    }

    private function cleanText(?string $text): string
    {
        $decoded = html_entity_decode($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_replace("\xc2\xa0", ' ', $decoded);

        return trim(preg_replace('/\s+/u', ' ', $decoded) ?? '');
    }

    private function normalizeName(string $name): string
    {
        $ascii = Str::ascii($name);
        $ascii = Str::lower($ascii);

        return preg_replace('/[^a-z0-9]+/', '', $ascii) ?? $ascii;
    }

    private function normalizeNumber(?string $value): ?string
    {
        $value = $this->cleanText($value);

        return preg_match('/^\d+$/', $value) ? $value : null;
    }

    private function normalizeAge(?string $value): ?int
    {
        $value = $this->cleanText($value);

        return preg_match('/^\d+$/', $value) ? (int) $value : null;
    }

    private function normalizeFoot(?string $value): ?string
    {
        return match (Str::lower($this->cleanText($value))) {
            'left' => 'left',
            'right' => 'right',
            'both' => 'both',
            default => null,
        };
    }

    private function normalizeHeight(?string $value): ?string
    {
        $value = $this->cleanText($value);
        if ($value === '' || $value === '-') {
            return null;
        }

        $value = str_replace('m', '', $value);

        return str_contains($value, ',') ? "{$value}m" : "{$value},00m";
    }

    private function normalizeDate(?string $value): ?string
    {
        $value = $this->cleanText($value);
        if ($value === '' || $value === '-' || $value === '?') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('d.m.Y', $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeContractDate(?string $value): ?string
    {
        $value = $this->cleanText($value);
        if ($value === '' || $value === '-' || $value === '?') {
            return null;
        }

        if (preg_match('/^\d{4}$/', $value)) {
            return "{$value}-06-30";
        }

        return $this->normalizeDate($value);
    }

    private function normalizeMarketValue(?string $value): ?string
    {
        return $this->formatMarketValueEuros($this->parseEuroAmount($value));
    }

    private function mapPosition(?string ...$candidates): string
    {
        $map = [
            'Goalkeeper' => 'Goalkeeper',
            'Defence' => 'Centre-Back',
            'Defence - Centre-Back' => 'Centre-Back',
            'Defence - Left-Back' => 'Left-Back',
            'Defence - Right-Back' => 'Right-Back',
            'Defence - Sweeper' => 'Centre-Back',
            'Midfield' => 'Central Midfield',
            'Midfield - Defensive Midfield' => 'Defensive Midfield',
            'Midfield - Central Midfield' => 'Central Midfield',
            'Midfield - Attacking Midfield' => 'Attacking Midfield',
            'Midfield - Left Midfield' => 'Left Midfield',
            'Midfield - Right Midfield' => 'Right Midfield',
            'Attack' => 'Centre-Forward',
            'Attack - Left Winger' => 'Left Winger',
            'Attack - Right Winger' => 'Right Winger',
            'Attack - Centre-Forward' => 'Centre-Forward',
            'Attack - Second Striker' => 'Second Striker',
            'Striker' => 'Centre-Forward',
            'Second Striker' => 'Second Striker',
            'Left Winger' => 'Left Winger',
            'Right Winger' => 'Right Winger',
            'Left-Back' => 'Left-Back',
            'Right-Back' => 'Right-Back',
            'Centre-Back' => 'Centre-Back',
            'Defensive Midfield' => 'Defensive Midfield',
            'Central Midfield' => 'Central Midfield',
            'Attacking Midfield' => 'Attacking Midfield',
            'Left Midfield' => 'Left Midfield',
            'Right Midfield' => 'Right Midfield',
            'Centre-Forward' => 'Centre-Forward',
        ];

        foreach ($candidates as $candidate) {
            $value = $this->cleanText($candidate);
            if ($value === '') {
                continue;
            }

            if (isset($map[$value])) {
                return $map[$value];
            }

            if (str_contains($value, ' - ')) {
                $lastSegment = Str::afterLast($value, ' - ');
                if (isset($map[$lastSegment])) {
                    return $map[$lastSegment];
                }
            }
        }

        return 'Central Midfield';
    }

    /**
     * @param  array<int, array<string, mixed>>  $clubs
     * @return array<int, array<string, mixed>>
     */
    private function fillMissingPlayerData(array $clubs): array
    {
        $heightByPosition = [];
        $allHeights = [];
        $footByPosition = [];
        $allFeet = [];

        foreach ($clubs as $club) {
            foreach ($club['players'] as $player) {
                $position = $player['position'] ?? 'Central Midfield';
                $heightCm = $this->heightToCentimeters($player['height'] ?? null);
                if ($heightCm !== null) {
                    $heightByPosition[$position][] = $heightCm;
                    $allHeights[] = $heightCm;
                }

                if (! empty($player['foot'])) {
                    $footByPosition[$position][] = $player['foot'];
                    $allFeet[] = $player['foot'];
                }
            }
        }

        $globalHeight = $this->median($allHeights) ?? 168;
        $globalFoot = $this->mode($allFeet) ?? 'right';

        foreach ($clubs as &$club) {
            $knownTotal = 0;
            $missingIndexes = [];

            foreach ($club['players'] as $index => $player) {
                $euros = $this->marketValueToEuros($player['marketValue'] ?? null);
                if ($euros !== null) {
                    $knownTotal += $euros;
                } else {
                    $missingIndexes[] = $index;
                }
            }

            $clubTotal = $club['_market_value_total_eur'] ?? null;
            $clubAverage = $club['_market_value_average_eur'] ?? null;
            $remaining = ($clubTotal !== null && count($missingIndexes) > 0)
                ? max(0, $clubTotal - $knownTotal)
                : null;

            $weightTotal = 0.0;
            $weights = [];
            foreach ($missingIndexes as $index) {
                $position = $club['players'][$index]['position'] ?? 'Central Midfield';
                $weight = self::POSITION_MARKET_WEIGHTS[$position] ?? 1.0;
                $weights[$index] = $weight;
                $weightTotal += $weight;
            }

            foreach ($club['players'] as $index => &$player) {
                $position = $player['position'] ?? 'Central Midfield';

                if (empty($player['marketValue'])) {
                    $weight = $weights[$index] ?? 1.0;
                    $estimatedEuros = null;

                    if ($remaining !== null && $remaining > 0 && $weightTotal > 0.0) {
                        $estimatedEuros = (int) round($remaining * ($weight / $weightTotal));
                    } elseif ($clubAverage !== null) {
                        $estimatedEuros = (int) round($clubAverage * $weight);
                    }

                    $player['marketValue'] = $this->formatMarketValueEuros(max(5_000, $estimatedEuros ?? 50_000));
                }

                if (empty($player['height'])) {
                    $medianHeight = $this->median($heightByPosition[$position] ?? []);
                    $player['height'] = $this->formatHeightCentimeters((int) round($medianHeight ?? $globalHeight));
                }

                if (empty($player['foot'])) {
                    $player['foot'] = $this->mode($footByPosition[$position] ?? [])
                        ?? $this->defaultFootForPosition($position)
                        ?? $globalFoot;
                }
            }
            unset($player);
        }
        unset($club);

        return $clubs;
    }

    private function parseEuroAmount(?string $value): ?int
    {
        $value = $this->cleanText($value);
        if ($value === '' || $value === '-' || $value === '?') {
            return null;
        }

        $numeric = preg_replace('/[^\d]/', '', $value);
        if ($numeric === null || $numeric === '') {
            return null;
        }

        return (int) $numeric;
    }

    private function marketValueToEuros(?string $value): ?int
    {
        $value = $this->cleanText($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d+(?:\.\d+)?)(m|k)?$/i', $value, $matches)) {
            $amount = (float) $matches[1];
            $suffix = Str::lower($matches[2] ?? '');

            return match ($suffix) {
                'm' => (int) round($amount * 1_000_000),
                'k' => (int) round($amount * 1_000),
                default => (int) round($amount),
            };
        }

        return $this->parseEuroAmount($value);
    }

    private function formatMarketValueEuros(?int $euros): ?string
    {
        if ($euros === null || $euros <= 0) {
            return null;
        }

        if ($euros >= 1_000_000) {
            $millions = round($euros / 1_000_000, 1);
            $formatted = rtrim(rtrim(number_format($millions, 1, '.', ''), '0'), '.');

            return "{$formatted}m";
        }

        if ($euros >= 1_000) {
            return (string) round($euros / 1_000) . 'k';
        }

        return (string) $euros;
    }

    private function heightToCentimeters(?string $value): ?int
    {
        $value = $this->cleanText($value);
        if ($value === '' || $value === '-') {
            return null;
        }

        if (preg_match('/^(\d),(\d{2})m$/', $value, $matches)) {
            return ((int) $matches[1] * 100) + (int) $matches[2];
        }

        return null;
    }

    private function formatHeightCentimeters(int $centimeters): string
    {
        return sprintf('%d,%02dm', intdiv($centimeters, 100), $centimeters % 100);
    }

    /**
     * @param  array<int, int>  $values
     */
    private function median(array $values): ?float
    {
        if (empty($values)) {
            return null;
        }

        sort($values);
        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    /**
     * @param  array<int, string>  $values
     */
    private function mode(array $values): ?string
    {
        if (empty($values)) {
            return null;
        }

        $counts = array_count_values($values);
        arsort($counts);

        return array_key_first($counts);
    }

    private function defaultFootForPosition(string $position): string
    {
        return match ($position) {
            'Left-Back', 'Left Midfield', 'Left Winger' => 'left',
            default => 'right',
        };
    }
}
