<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string|null $external_source
 * @property string|null $external_id
 * @property string $name
 * @property string $country
 * @property string|null $image
 * @property string|null $stadium_name
 * @property int $stadium_seats
 * @property array|null $colors
 * @property-read \App\Models\ClubProfile|null $clubProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition> $competitions
 * @property-read int|null $competitions_count
 * @property-read int $goal_difference
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GamePlayer> $players
 * @property-read int|null $players_count
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereStadiumName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereStadiumSeats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereExternalId($value)
 * @mixin \Eloquent
 */
class Team extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    /** @var array<string, string>|null */
    private static ?array $localCrestMap = null;

    protected $fillable = [
        'external_source',
        'external_id',
        'type',
        'name',
        'country',
        'parent_team_id',
        'image',
        'stadium_name',
        'stadium_seats',
        'colors',
    ];

    protected $casts = [
        'stadium_seats' => 'integer',
        'colors' => 'array',
    ];

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'competition_teams')
            ->withPivot('season');
    }

    public function players(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function clubProfile(): HasOne
    {
        return $this->hasOne(ClubProfile::class);
    }

    public function parentTeam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    public function reserveTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'parent_team_id');
    }

    public function isReserveTeam(): bool
    {
        return $this->parent_team_id !== null;
    }

    public function getNameAttribute(): string
    {
        $name = $this->attributes['name'] ?? '';

        if (($this->attributes['type'] ?? 'club') === 'national') {
            return __("countries.{$name}") ?? $name;
        }

        return $name;
    }

    public function getImageAttribute(): ?string
    {
        // National teams use flag SVGs
        if (($this->attributes['type'] ?? 'club') === 'national') {
            return Storage::disk('assets')->url('flags/' . strtolower($this->country) . '.svg');
        }

        $originalUrl = $this->attributes['image'] ?? null;

        if ($localPath = $this->resolveNamedLocalCrestPath()) {
            return Storage::disk('assets')->url($localPath);
        }

        if ($this->external_id) {
            foreach (['png', 'svg'] as $extension) {
                $localPath = "crests/{$this->external_id}.{$extension}";
                if (file_exists(public_path($localPath))) {
                    return Storage::disk('assets')->url($localPath);
                }
            }
        }

        return $originalUrl;
    }

    private function resolveNamedLocalCrestPath(): ?string
    {
        $name = $this->attributes['name'] ?? '';
        $key = self::normalizeCrestKey($name);

        if ($key === '') {
            return null;
        }

        return self::localCrestMap()[$key] ?? null;
    }

    /**
     * Build a lookup map for manual crest files named by club slug, optionally
     * prefixed with a competition code such as "esp1-" or "eng1-".
     *
     * @return array<string, string>
     */
    private static function localCrestMap(): array
    {
        if (self::$localCrestMap !== null) {
            return self::$localCrestMap;
        }

        $crestsDir = public_path('crests');
        if (! is_dir($crestsDir)) {
            return self::$localCrestMap = [];
        }

        $map = [];

        foreach (scandir($crestsDir) ?: [] as $file) {
            $path = $crestsDir . DIRECTORY_SEPARATOR . $file;
            if (! is_file($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (! in_array($extension, ['png', 'svg'], true)) {
                continue;
            }

            $basename = pathinfo($file, PATHINFO_FILENAME);
            $relativePath = "crests/{$file}";

            $keys = [self::normalizeCrestKey($basename)];

            if (preg_match('/^[a-z0-9]+-(.+)$/i', $basename, $matches) === 1) {
                $keys[] = self::normalizeCrestKey($matches[1]);
            }

            foreach (array_filter(array_unique($keys)) as $key) {
                $map[$key] ??= $relativePath;
            }
        }

        return self::$localCrestMap = $map;
    }

    private static function normalizeCrestKey(string $value): string
    {
        return trim(Str::slug(Str::ascii($value)));
    }

    public function getTransfermarktIdAttribute(): ?string
    {
        return $this->attributes['external_id'] ?? null;
    }

    public function getGoalDifferenceAttribute(): int
    {
        return 0; // Placeholder for team-level stats
    }

    /**
     * Get the Spanish grammatical article for this team name.
     *
     * Most teams use "el" (masculine): "del Real Madrid", "al Barcelona"
     * Some use "la" (feminine): "de la Real Sociedad", "a la UD AlmerÃ­a"
     * Some use no article: "de Osasuna", "a Osasuna"
     */
    public function getArticleAttribute(): ?string
    {
        $name = $this->attributes['name'] ?? '';

        if ($name === 'CA Osasuna') {
            return null;
        }

        if (str_starts_with($name, 'UD ') || str_starts_with($name, 'Real Sociedad') || $name === 'Cultural Leonesa') {
            return 'la';
        }

        return 'el';
    }

    /**
     * Team name with "de" preposition: "del Real Madrid", "de la Real Sociedad", "de Osasuna"
     */
    public function nameWithDe(): string
    {
        return match ($this->article) {
            'la' => 'de la ' . $this->name,
            null => 'de ' . $this->name,
            default => 'del ' . $this->name,
        };
    }

    /**
     * Team name with "a" preposition: "al Real Madrid", "a la Real Sociedad", "a Osasuna"
     */
    public function nameWithA(): string
    {
        return match ($this->article) {
            'la' => 'a la ' . $this->name,
            null => 'a ' . $this->name,
            default => 'al ' . $this->name,
        };
    }

    /**
     * Team name with "en" preposition: "en el Real Madrid", "en la Real Sociedad", "en Osasuna"
     */
    public function nameWithEn(): string
    {
        return match ($this->article) {
            'la' => 'en la ' . $this->name,
            null => 'en ' . $this->name,
            default => 'en el ' . $this->name,
        };
    }
}
