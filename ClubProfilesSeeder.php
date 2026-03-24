<?php

namespace Database\Seeders;

use App\Models\ClubProfile;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ClubProfilesSeeder extends Seeder
{
    /**
     * Club profiles with reputation level.
     * Commercial revenue is now calculated algorithmically from stadium_seats Ã— config rate.
     *
     * Names must match the database exactly (seeded from Transfermarkt JSON data).
     */
    private const CLUB_DATA = [
        // =============================================
        // Spain - La Liga (ESP1)
        // =============================================

        // Elite - Objetivo: Liga
        'Real Madrid' => ClubProfile::REPUTATION_ELITE,
        'FC Barcelona' => ClubProfile::REPUTATION_ELITE,
        'AtlÃ©tico de Madrid' => ClubProfile::REPUTATION_ELITE,

        // Continental - Objetivo: Europa League
        'Athletic Bilbao' => ClubProfile::REPUTATION_CONTINENTAL,
        'Villarreal CF' => ClubProfile::REPUTATION_CONTINENTAL,
        'Real Betis BalompiÃ©' => ClubProfile::REPUTATION_CONTINENTAL,
        'Sevilla FC' => ClubProfile::REPUTATION_CONTINENTAL,
        'Real Sociedad' => ClubProfile::REPUTATION_CONTINENTAL,

        // Established - Objetivo: Top 10
        'Valencia CF' => ClubProfile::REPUTATION_ESTABLISHED,
        'RCD Espanyol Barcelona' => ClubProfile::REPUTATION_ESTABLISHED,
        'Celta de Vigo' => ClubProfile::REPUTATION_ESTABLISHED,
        'RCD Mallorca' => ClubProfile::REPUTATION_ESTABLISHED,
        'CA Osasuna' => ClubProfile::REPUTATION_ESTABLISHED,
        'Getafe CF' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest - Objetivo: No descender
        'Rayo Vallecano' => ClubProfile::REPUTATION_MODEST,
        'Girona FC' => ClubProfile::REPUTATION_MODEST,
        'Deportivo AlavÃ©s' => ClubProfile::REPUTATION_MODEST,
        'Elche CF' => ClubProfile::REPUTATION_MODEST,
        'Levante UD' => ClubProfile::REPUTATION_MODEST,
        'Real Oviedo' => ClubProfile::REPUTATION_MODEST,

        // =============================================
        // Spain - La Liga 2 (ESP2)
        // =============================================

        // Established (historic clubs) - Objetivo: Playoff ascenso
        'Deportivo de La CoruÃ±a' => ClubProfile::REPUTATION_ESTABLISHED,
        'MÃ¡laga CF' => ClubProfile::REPUTATION_ESTABLISHED,
        'Sporting GijÃ³n' => ClubProfile::REPUTATION_ESTABLISHED,
        'UD Las Palmas' => ClubProfile::REPUTATION_ESTABLISHED,
        'Real Valladolid CF' => ClubProfile::REPUTATION_ESTABLISHED,
        'Granada CF' => ClubProfile::REPUTATION_ESTABLISHED,
        'CÃ¡diz CF' => ClubProfile::REPUTATION_ESTABLISHED,
        'Racing Santander' => ClubProfile::REPUTATION_ESTABLISHED,
        'UD AlmerÃ­a' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest - Objetivo: Top 10
        'Real Zaragoza' => ClubProfile::REPUTATION_MODEST,
        'CÃ³rdoba CF' => ClubProfile::REPUTATION_MODEST,
        'CD CastellÃ³n' => ClubProfile::REPUTATION_MODEST,
        'Albacete BalompiÃ©' => ClubProfile::REPUTATION_MODEST,
        'SD Huesca' => ClubProfile::REPUTATION_MODEST,
        'SD Eibar' => ClubProfile::REPUTATION_MODEST,
        'CD LeganÃ©s' => ClubProfile::REPUTATION_MODEST,

        // Local - Objetivo: No descender
        'Burgos CF' => ClubProfile::REPUTATION_LOCAL,
        'Cultural Leonesa' => ClubProfile::REPUTATION_LOCAL,
        'CD MirandÃ©s' => ClubProfile::REPUTATION_LOCAL,
        'AD Ceuta FC' => ClubProfile::REPUTATION_LOCAL,
        'FC Andorra' => ClubProfile::REPUTATION_LOCAL,
        'Real Sociedad B' => ClubProfile::REPUTATION_LOCAL,

        // =============================================
        // England - Premier League (ENG1)
        // =============================================

        // Elite
        'Arsenal Women' => ClubProfile::REPUTATION_ELITE,
        'Chelsea Women' => ClubProfile::REPUTATION_ELITE,
        'Manchester City Women' => ClubProfile::REPUTATION_ELITE,

        // Continental
        'Manchester United Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'Liverpool Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'Tottenham Hotspur Women' => ClubProfile::REPUTATION_CONTINENTAL,

        // Established
        'Aston Villa Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Brighton & Hove Albion Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Everton Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'West Ham United Women' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest
        'Leicester City Women' => ClubProfile::REPUTATION_MODEST,
        'London City Lionesses' => ClubProfile::REPUTATION_MODEST,

        // =============================================
        // Germany - Bundesliga (DEU1)
        // =============================================

        // Elite
        'Bayern Munich Women' => ClubProfile::REPUTATION_ELITE,
        'Wolfsburg Women' => ClubProfile::REPUTATION_ELITE,

        // Continental
        'Eintracht Frankfurt Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'Bayer 04 Leverkusen Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'Hoffenheim Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'RB Leipzig Women' => ClubProfile::REPUTATION_CONTINENTAL,

        // Established
        'SC Freiburg Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Werder Bremen Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'FC Koln Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Hamburger SV Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'SGS Essen' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest
        'Union Berlin Women' => ClubProfile::REPUTATION_MODEST,
        'Carl Zeiss Jena Women' => ClubProfile::REPUTATION_MODEST,
        'FC Nurnberg Women' => ClubProfile::REPUTATION_MODEST,

        // =============================================
        // France - Ligue 1 (FRA1)
        // =============================================

        // Elite
        'OL Lyonnes' => ClubProfile::REPUTATION_ELITE,
        'Paris Saint-Germain Women' => ClubProfile::REPUTATION_ELITE,

        // Continental
        'Paris FC Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'FC Fleury 91' => ClubProfile::REPUTATION_CONTINENTAL,
        'Olympique de Marseille Women' => ClubProfile::REPUTATION_CONTINENTAL,

        // Established
        'Montpellier HSC Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'FC Nantes Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'RC Lens Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Dijon FCO Women' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest
        'Le Havre Women' => ClubProfile::REPUTATION_MODEST,
        'Racing Strasbourg Women' => ClubProfile::REPUTATION_MODEST,
        'AS Saint-Etienne Women' => ClubProfile::REPUTATION_MODEST,

        // =============================================
        // Italy - Serie A (ITA1)
        // =============================================

        // Elite
        'Juventus Women' => ClubProfile::REPUTATION_ELITE,
        'Inter Women' => ClubProfile::REPUTATION_ELITE,
        'AS Roma Women' => ClubProfile::REPUTATION_ELITE,

        // Continental
        'AC Milan Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'Fiorentina Women' => ClubProfile::REPUTATION_CONTINENTAL,
        'Napoli Women' => ClubProfile::REPUTATION_CONTINENTAL,

        // Established
        'Lazio Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Sassuolo Women' => ClubProfile::REPUTATION_ESTABLISHED,
        'Como Women' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest
        'Parma Women' => ClubProfile::REPUTATION_MODEST,
        'Genoa Women' => ClubProfile::REPUTATION_MODEST,
        'Ternana Women' => ClubProfile::REPUTATION_MODEST,

        // =============================================
        // European transfer pool (EUR)
        // =============================================

        // Continental
        'SL Benfica' => ClubProfile::REPUTATION_CONTINENTAL,
        'FC Porto' => ClubProfile::REPUTATION_CONTINENTAL,
        'Ajax Amsterdam' => ClubProfile::REPUTATION_CONTINENTAL,
        'Galatasaray' => ClubProfile::REPUTATION_CONTINENTAL,
        'Sporting CP' => ClubProfile::REPUTATION_CONTINENTAL,
        'Celtic FC' => ClubProfile::REPUTATION_CONTINENTAL,
        'Fenerbahce' => ClubProfile::REPUTATION_CONTINENTAL,
        'Feyenoord Rotterdam' => ClubProfile::REPUTATION_CONTINENTAL,
        'PSV Eindhoven' => ClubProfile::REPUTATION_CONTINENTAL,
        'Olympiacos Piraeus' => ClubProfile::REPUTATION_CONTINENTAL,
        'Red Bull Salzburg' => ClubProfile::REPUTATION_CONTINENTAL,

        // Established
        'Club Brugge KV' => ClubProfile::REPUTATION_ESTABLISHED,
        'SC Braga' => ClubProfile::REPUTATION_ESTABLISHED,
        'FC Copenhagen' => ClubProfile::REPUTATION_ESTABLISHED,
        'Rangers FC' => ClubProfile::REPUTATION_ESTABLISHED,
        'Red Star Belgrade' => ClubProfile::REPUTATION_ESTABLISHED,
        'SK Slavia Prague' => ClubProfile::REPUTATION_ESTABLISHED,
        'FerencvÃ¡rosi TC' => ClubProfile::REPUTATION_ESTABLISHED,
        'SK Sturm Graz' => ClubProfile::REPUTATION_ESTABLISHED,
        'FC Basel 1893' => ClubProfile::REPUTATION_ESTABLISHED,
        'PAOK Thessaloniki' => ClubProfile::REPUTATION_ESTABLISHED,
        'Panathinaikos FC' => ClubProfile::REPUTATION_ESTABLISHED,
        'GNK Dinamo Zagreb' => ClubProfile::REPUTATION_ESTABLISHED,
        'BSC Young Boys' => ClubProfile::REPUTATION_ESTABLISHED,

        // Modest
        'KRC Genk' => ClubProfile::REPUTATION_MODEST,
        'Union Saint-Gilloise' => ClubProfile::REPUTATION_MODEST,
        'MalmÃ¶ FF' => ClubProfile::REPUTATION_MODEST,
        'FK BodÃ¸/Glimt' => ClubProfile::REPUTATION_MODEST,
        'FC Midtjylland' => ClubProfile::REPUTATION_MODEST,
        'FCSB' => ClubProfile::REPUTATION_MODEST,
        'FC Viktoria Plzen' => ClubProfile::REPUTATION_MODEST,
        'Ludogorets Razgrad' => ClubProfile::REPUTATION_MODEST,
        'Maccabi Tel Aviv' => ClubProfile::REPUTATION_MODEST,
        'FC Utrecht' => ClubProfile::REPUTATION_MODEST,
        'SK Brann' => ClubProfile::REPUTATION_MODEST,
        'QarabaÄŸ FK' => ClubProfile::REPUTATION_MODEST,
        'Go Ahead Eagles' => ClubProfile::REPUTATION_MODEST,
        'Pafos FC' => ClubProfile::REPUTATION_MODEST,
        'Kairat Almaty' => ClubProfile::REPUTATION_MODEST,
    ];

    public function run(): void
    {
        // Seed club profiles for all teams that match known names
        $allTeams = Team::all();
        $seeded = 0;

        foreach ($allTeams as $team) {
            $reputation = self::CLUB_DATA[$team->name] ?? ClubProfile::REPUTATION_LOCAL;

            ClubProfile::updateOrCreate(
                ['team_id' => $team->id],
                [
                    'reputation_level' => $reputation,
                ]
            );

            $seeded++;
        }

        $this->command->info('Club profiles seeded for ' . $seeded . ' teams');
    }
}

