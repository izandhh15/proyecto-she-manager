<?php

namespace App\Console\Commands;

use App\Support\CountryCodeMapper;
use App\Support\ExternalData;
use App\Support\SoccerdonnaPlayerOverrides;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportSoccerdonnaNationalTeams extends Command
{
    protected $signature = 'app:import-soccerdonna-national-teams
                            {team?* : FIFA codes or country names to import (default: all real teams in the competition CSV)}
                            {--season=2025 : Season folder to update}
                            {--competition=WC2026 : Competition folder to update}
                            {--all-published : Import every women national team currently published by Soccerdonna}
                            {--dry-run : Fetch and parse without writing files}
                            {--team-limit= : Limit imported teams for testing}
                            {--sleep-ms=150 : Delay between HTTP requests in milliseconds}';

    protected $description = 'Refresh women national team rosters from Soccerdonna into WC2026-style data files';

    private const NATIONAL_TEAMS_URL = 'https://www.soccerdonna.de/en/nationalmannschaften/startseite/nationalmannschaften.html';

    /** @var array<string, array<int, string>> */
    private const TEAM_NAME_ALIASES = [
        'CPV' => ['Cape Verde', 'Cabo Verde'],
        'CIV' => ["Ivory Coast", "Cote d'Ivoire", "Côte d'Ivoire"],
        'CUR' => ['Curacao', 'Curaçao', 'CuraÃ§ao'],
        'KOR' => ['South Korea', 'Korea, South', 'Korea Republic', 'Republic of Korea'],
        'NED' => ['Netherlands', 'Holland'],
        'RSA' => ['South Africa'],
        'SCO' => ['Scotland', 'Scottland'],
        'USA' => ['United States', 'USA'],
    ];

    /** @var array<string, string> */
    private const CSV_TEAM_NAME_OVERRIDES = [
        'CUR' => 'Curaçao',
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
        $requestedTeams = $this->argument('team');
        $season = (string) $this->option('season');
        $competition = Str::upper((string) $this->option('competition'));
        $allPublished = (bool) $this->option('all-published');
        $dryRun = (bool) $this->option('dry-run');
        $teamLimit = $this->option('team-limit');
        $teamLimit = $teamLimit !== null ? max(1, (int) $teamLimit) : null;

        $this->season = $season;
        $this->sleepMs = max(0, (int) $this->option('sleep-ms'));

        try {
            $this->importCompetition($competition, $season, $requestedTeams, $teamLimit, $dryRun, $allPublished);
        } catch (\Throwable $e) {
            $this->error("FAILED {$competition}: {$e->getMessage()}");
            $this->error("  at {$e->getFile()}:{$e->getLine()}");

            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }

    /**
     * @param  array<int, string>  $requestedTeams
     */
    private function importCompetition(string $competition, string $season, array $requestedTeams, ?int $teamLimit, bool $dryRun, bool $allPublished): void
    {
        $teamsDir = base_path("data/{$season}/{$competition}/teams");
        $rawTeamsPath = base_path("data/{$season}/{$competition}/raw/teams.csv");

        if (! $allPublished && ! file_exists($rawTeamsPath)) {
            throw new \RuntimeException("Competition CSV not found: {$rawTeamsPath}");
        }

        $csvTeams = $allPublished ? [] : $this->loadCompetitionTeams($rawTeamsPath);
        if (! $allPublished && ! empty($requestedTeams)) {
            $csvTeams = array_values(array_filter(
                $csvTeams,
                fn (array $team) => $this->matchesRequestedTeam($team, $requestedTeams)
            ));

            if (empty($csvTeams)) {
                throw new \RuntimeException('None of the requested teams were found in the competition CSV.');
            }
        }

        if (! $allPublished && $teamLimit !== null) {
            $csvTeams = array_slice($csvTeams, 0, $teamLimit);
        }

        $existingByName = $this->loadExistingTeamFiles($teamsDir);
        $refs = $this->fetchNationalTeamRefs(self::NATIONAL_TEAMS_URL);

        if ($allPublished) {
            if (! empty($requestedTeams)) {
                $refs = array_values(array_filter(
                    $refs,
                    fn (array $ref) => $this->matchesRequestedNationalTeamRef($ref, $requestedTeams)
                ));

                if (empty($refs)) {
                    throw new \RuntimeException('None of the requested teams were found on Soccerdonna.');
                }
            }

            if ($teamLimit !== null) {
                $refs = array_slice($refs, 0, $teamLimit);
            }
        }

        $this->newLine();
        $this->info('Importing ' . ($allPublished ? 'all published' : $competition) . ' national teams from Soccerdonna...');

        $payloads = [];
        $unmatched = [];

        if ($allPublished) {
            foreach ($refs as $index => $ref) {
                $this->line(sprintf('  [%d/%d] %s', $index + 1, count($refs), $ref['name']));

                $players = $this->fetchNationalTeamPlayers($ref['squad_url'], $ref['name']);

                $payloads[] = [
                    'filename' => "{$ref['id']}.json",
                    'club' => [
                        'id' => $ref['id'],
                        'externalId' => $ref['id'],
                        'transfermarktId' => $ref['id'],
                        'name' => $ref['name'],
                        'image' => $ref['image'] ?? ($existingByName[$ref['name']]['image'] ?? null),
                        'players' => $players,
                        '_market_value_total_eur' => null,
                        '_market_value_average_eur' => null,
                    ],
                ];
            }
        }

        foreach ($csvTeams as $index => $team) {
            $ref = $this->matchNationalTeamRef($team, $refs);
            if (! $ref) {
                $unmatched[] = "{$team['fifa_code']} {$team['name']}";
                $this->warn(sprintf('  [%d/%d] %s -> no Soccerdonna national team match', $index + 1, count($csvTeams), $team['name']));

                continue;
            }

            $this->line(sprintf('  [%d/%d] %s -> %s', $index + 1, count($csvTeams), $team['name'], $ref['name']));

            $players = $this->fetchNationalTeamPlayers($ref['squad_url'], $team['name']);

            $payloads[] = [
                'filename' => "{$ref['id']}.json",
                'club' => [
                    'id' => $ref['id'],
                    'externalId' => $ref['id'],
                    'transfermarktId' => $ref['id'],
                    'name' => $team['name'],
                    'image' => $ref['image'] ?? ($existingByName[$team['name']]['image'] ?? null),
                    'players' => $players,
                    '_market_value_total_eur' => null,
                    '_market_value_average_eur' => null,
                ],
            ];
        }

        if (! empty($unmatched)) {
            throw new \RuntimeException('Unmatched teams: ' . implode(', ', $unmatched));
        }

        $clubs = $this->fillMissingPlayerData(array_map(fn (array $payload) => $payload['club'], $payloads));
        foreach ($payloads as $index => $payload) {
            $payloads[$index]['club'] = $clubs[$index];
            unset(
                $payloads[$index]['club']['_market_value_total_eur'],
                $payloads[$index]['club']['_market_value_average_eur']
            );
        }

        if (! $dryRun) {
            if (! is_dir($teamsDir)) {
                mkdir($teamsDir, 0777, true);
            }

            $importedFilenames = array_column($payloads, 'filename');
            if (empty($requestedTeams)) {
                foreach (glob("{$teamsDir}/*.json") as $existingFile) {
                    if (! in_array(basename($existingFile), $importedFilenames, true)) {
                        unlink($existingFile);
                    }
                }
            }

            foreach ($payloads as $payload) {
                file_put_contents(
                    "{$teamsDir}/{$payload['filename']}",
                    json_encode($payload['club'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL
                );
            }
        }

        $playerCount = array_sum(array_map(fn (array $payload) => count($payload['club']['players']), $payloads));
        $suffix = $dryRun ? ' (dry-run)' : '';
        $this->info("Completed {$competition}: " . count($payloads) . " teams, {$playerCount} players{$suffix}");
    }

    /**
     * @return array<int, array{csv_id: int, fifa_code: string, name: string}>
     */
    private function loadCompetitionTeams(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV: {$path}");
        }

        $teams = [];
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $isPlaceholder = Str::lower(trim((string) ($row[4] ?? ''))) === 'true';
            if ($isPlaceholder) {
                continue;
            }

            $fifaCode = Str::upper(trim((string) ($row[2] ?? '')));

            $teams[] = [
                'csv_id' => (int) ($row[0] ?? 0),
                'name' => self::CSV_TEAM_NAME_OVERRIDES[$fifaCode]
                    ?? $this->normalizeCsvCell((string) ($row[1] ?? '')),
                'fifa_code' => $fifaCode,
            ];
        }

        fclose($handle);

        return $teams;
    }

    /**
     * @param  array<int, string>  $requestedTeams
     */
    private function matchesRequestedTeam(array $team, array $requestedTeams): bool
    {
        $requestedCodes = array_map(fn (string $value) => Str::upper(trim($value)), $requestedTeams);
        if (in_array($team['fifa_code'], $requestedCodes, true)) {
            return true;
        }

        $normalizedRequested = array_map(fn (string $value) => $this->normalizeName($value), $requestedTeams);

        return in_array($this->normalizeName($team['name']), $normalizedRequested, true);
    }

    /**
     * @param  array{id: string, name: string, normalized_name: string, country_code: ?string, image: ?string, squad_url: string}  $ref
     * @param  array<int, string>  $requestedTeams
     */
    private function matchesRequestedNationalTeamRef(array $ref, array $requestedTeams): bool
    {
        $requestedCodes = array_map(fn (string $value) => Str::upper(trim($value)), $requestedTeams);
        if (in_array(Str::upper((string) ($ref['country_code'] ?? '')), $requestedCodes, true)) {
            return true;
        }

        $normalizedRequested = array_map(fn (string $value) => $this->normalizeName($value), $requestedTeams);

        return in_array($ref['normalized_name'], $normalizedRequested, true);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadExistingTeamFiles(string $teamsDir): array
    {
        if (! is_dir($teamsDir)) {
            return [];
        }

        $existing = [];
        foreach (glob("{$teamsDir}/*.json") as $filePath) {
            try {
                $data = ExternalData::decodeJsonFile($filePath);
            } catch (\RuntimeException) {
                continue;
            }

            if (! empty($data['name'])) {
                $existing[$data['name']] = $data;
            }
        }

        return $existing;
    }

    /**
     * @return array<int, array{id: string, name: string, normalized_name: string, country_code: ?string, image: ?string, squad_url: string}>
     */
    private function fetchNationalTeamRefs(string $url): array
    {
        $xpath = $this->loadXPath($this->fetchHtml($url));
        $links = $xpath->query("//a[contains(@href, '/startseite/nationalmannschaft_')]");

        $refsById = [];
        foreach ($links as $link) {
            $href = $link->attributes?->getNamedItem('href')?->nodeValue;
            if (! $href || ! preg_match('/nationalmannschaft_(\d+)\.html$/', $href, $matches)) {
                continue;
            }

            $id = $matches[1];
            if (isset($refsById[$id])) {
                continue;
            }

            $name = $this->cleanText($link->textContent);
            if ($name === '') {
                continue;
            }

            $row = $this->closestElement($link, 'tr');
            $image = null;
            if ($row) {
                $flag = $xpath->query(".//img[@src][1]", $row)->item(0);
                $src = $flag?->attributes?->getNamedItem('src')?->nodeValue;
                $image = $src ? $this->absoluteUrl($src) : null;
            }

            $refsById[$id] = [
                'id' => $id,
                'name' => $name,
                'normalized_name' => $this->normalizeName($name),
                'country_code' => CountryCodeMapper::toCode($name),
                'image' => $image,
                'squad_url' => $this->absoluteUrl(str_replace('/startseite/', '/kader/', $href)),
            ];
        }

        if (empty($refsById)) {
            throw new \RuntimeException("No national team links parsed from page: {$url}");
        }

        return array_values($refsById);
    }

    /**
     * @param  array{fifa_code: string, name: string}  $team
     * @param  array<int, array{id: string, name: string, normalized_name: string, country_code: ?string, image: ?string, squad_url: string}>  $refs
     * @return array{id: string, name: string, normalized_name: string, country_code: ?string, image: ?string, squad_url: string}|null
     */
    private function matchNationalTeamRef(array $team, array $refs): ?array
    {
        $candidateNames = array_values(array_unique([
            $team['name'],
            ...self::TEAM_NAME_ALIASES[$team['fifa_code']] ?? [],
        ]));

        foreach ($candidateNames as $candidateName) {
            $normalizedCandidate = $this->normalizeName($candidateName);
            foreach ($refs as $ref) {
                if ($ref['normalized_name'] === $normalizedCandidate) {
                    return $ref;
                }
            }
        }

        $teamCountryCode = CountryCodeMapper::toCode($team['name']);
        if ($teamCountryCode) {
            $codeMatches = array_values(array_filter(
                $refs,
                fn (array $ref) => $ref['country_code'] === $teamCountryCode
            ));

            if (count($codeMatches) === 1) {
                return $codeMatches[0];
            }

            foreach ($candidateNames as $candidateName) {
                $normalizedCandidate = $this->normalizeName($candidateName);
                foreach ($codeMatches as $match) {
                    if ($match['normalized_name'] === $normalizedCandidate) {
                        return $match;
                    }
                }
            }

            if (! empty($codeMatches)) {
                return $codeMatches[0];
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchNationalTeamPlayers(string $url, string $teamName): array
    {
        $xpath = $this->loadXPath($this->fetchHtml($url));
        $table = $xpath->query("//table[@id='spieler']")->item(0);

        if (! $table) {
            throw new \RuntimeException("Could not find squad table on national team page: {$url}");
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
            if ($cells->length < 4) {
                continue;
            }

            $profile = $this->fetchPlayerProfile($this->absoluteUrl($profileHref));

            $player = [
                'id' => $matches[1],
                'externalId' => $matches[1],
                'name' => $profile['name'] ?? $this->cleanText($profileLink->textContent),
                'position' => $this->mapPosition(
                    $profile['position'] ?? null,
                    $this->cleanText($xpath->query(".//td[1]//tr[2]/td[1]", $row)->item(0)?->textContent ?? '')
                ),
                'age' => $profile['date_of_birth']
                    ? CarbonImmutable::parse($profile['date_of_birth'])->age
                    : $this->normalizeAge($cells->item(3)->textContent),
                'nationality' => $profile['nationality'] ?: [$teamName],
                'dateOfBirth' => $profile['date_of_birth'],
                'foot' => $this->normalizeFoot($profile['foot'] ?? $cells->item(2)->textContent),
                'height' => $this->normalizeHeight($profile['height'] ?? $cells->item(1)->textContent),
                'marketValue' => $profile['market_value'],
                'contract' => $profile['contract_until'],
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

    private function closestElement(\DOMNode $node, string $tagName): ?\DOMElement
    {
        $current = $node;

        while ($current = $current->parentNode) {
            if ($current instanceof \DOMElement && Str::lower($current->tagName) === Str::lower($tagName)) {
                return $current;
            }
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

    private function normalizeCsvCell(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        $value = strtr($value, [
            'CuraÃ§ao' => 'Curaçao',
            'CuraÃƒÂ§ao' => 'Curaçao',
            "CÃ´te d'Ivoire" => "Côte d'Ivoire",
        ]);

        if (preg_match('/Ã|Â|â/u', $value)) {
            $repaired = @mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
            if (is_string($repaired) && $repaired !== '') {
                return trim($repaired);
            }
        }

        return $value;
    }

    private function normalizeName(string $name): string
    {
        $ascii = Str::ascii($name);
        $ascii = Str::lower($ascii);

        return preg_replace('/[^a-z0-9]+/', '', $ascii) ?? $ascii;
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
