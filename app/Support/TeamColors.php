<?php

namespace App\Support;

class TeamColors
{
    /**
     * Tailwind color name Ã¢â€ â€™ hex value lookup.
     * Only includes shades actually used by teams.
     */
    private const TAILWIND_HEX = [
        'white' => '#FFFFFF',
        'black' => '#000000',
        'gray-900' => '#111827',
        'red-500' => '#EF4444',
        'red-600' => '#DC2626',
        'red-700' => '#B91C1C',
        'red-800' => '#991B1B',
        'rose-800' => '#9F1239',
        'orange-500' => '#F97316',
        'amber-400' => '#FBBF24',
        'amber-500' => '#F59E0B',
        'amber-600' => '#D97706',
        'yellow-400' => '#FACC15',
        'yellow-500' => '#EAB308',
        'lime-500' => '#84CC16',
        'green-600' => '#16A34A',
        'green-700' => '#15803D',
        'emerald-600' => '#059669',
        'sky-400' => '#38BDF8',
        'sky-500' => '#0EA5E9',
        'blue-500' => '#3B82F6',
        'blue-600' => '#2563EB',
        'blue-700' => '#1D4ED8',
        'blue-800' => '#1E40AF',
        'blue-900' => '#1E3A8A',
        'purple-600' => '#9333EA',
        'purple-700' => '#7E22CE',
        'purple-800' => '#6B21A8',
        'red-900' => '#7F1D1D',
        'emerald-700' => '#047857',
        'teal-700' => '#0F766E',
        'sky-600' => '#0284C7',
        'indigo-700' => '#4338CA',
        'orange-600' => '#EA580C',
    ];

    /**
     * Team name Ã¢â€ â€™ kit colors mapping.
     * Uses Tailwind color names for readability.
     *
     * Patterns: solid, stripes, hoops, sash, halves
     */
    private const TEAMS = [
        // =============================================
        // Spain Ã¢â‚¬â€ La Liga (ESP1)
        // =============================================
        'Real Madrid' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'white',
            'number' => 'purple-800',
        ],
        'FC Barcelona' => [
            'pattern' => 'stripes',
            'primary' => 'rose-800',
            'secondary' => 'blue-800',
            'number' => 'white',
        ],
        'AtlÃƒÂ©tico de Madrid' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'blue-700',
        ],
        'Athletic Bilbao' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'black',
        ],
        'Real Betis BalompiÃƒÂ©' => [
            'pattern' => 'stripes',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Real Sociedad' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Sevilla FC' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Valencia CF' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'orange-500',
            'number' => 'black',
        ],
        'Villarreal CF' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'yellow-400',
            'number' => 'blue-900',
        ],
        'Celta de Vigo' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'sky-400',
            'number' => 'white',
        ],
        'RCD Espanyol Barcelona' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'CA Osasuna' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'blue-900',
            'number' => 'white',
        ],
        'Getafe CF' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'blue-600',
            'number' => 'white',
        ],
        'RCD Mallorca' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'Rayo Vallecano' => [
            'pattern' => 'sash',
            'primary' => 'white',
            'secondary' => 'red-500',
            'number' => 'black',
        ],
        'Deportivo AlavÃƒÂ©s' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Girona FC' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'red-600',
        ],
        'Levante UD' => [
            'pattern' => 'stripes',
            'primary' => 'blue-900',
            'secondary' => 'rose-800',
            'number' => 'yellow-400',
        ],
        'Elche CF' => [
            'pattern' => 'hoops',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Real Oviedo' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],

        // =============================================
        // Spain Ã¢â‚¬â€ Segunda DivisiÃƒÂ³n (ESP2)
        // =============================================
        'Deportivo de La CoruÃƒÂ±a' => [
            'pattern' => 'stripes',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'UD Las Palmas' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'yellow-400',
            'number' => 'blue-900',
        ],
        'MÃƒÂ¡laga CF' => [
            'pattern' => 'stripes',
            'primary' => 'blue-500',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Sporting GijÃƒÂ³n' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'black',
        ],
        'Real Valladolid CF' => [
            'pattern' => 'stripes',
            'primary' => 'purple-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Racing Santander' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'green-700',
        ],
        'CÃƒÂ³rdoba CF' => [
            'pattern' => 'stripes',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'green-600',
        ],
        'CÃƒÂ¡diz CF' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'Real Zaragoza' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'Granada CF' => [
            'pattern' => 'hoops',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'red-600',
        ],
        'UD AlmerÃƒÂ­a' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Albacete BalompiÃƒÂ©' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'white',
            'number' => 'black',
        ],
        'CD CastellÃƒÂ³n' => [
            'pattern' => 'stripes',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'black',
        ],
        'Cultural Leonesa' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-700',
            'number' => 'red-700',
        ],
        'CD LeganÃƒÂ©s' => [
            'pattern' => 'stripes',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Burgos CF' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'SD Huesca' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'SD Eibar' => [
            'pattern' => 'stripes',
            'primary' => 'blue-900',
            'secondary' => 'rose-800',
            'number' => 'white',
        ],
        'AD Ceuta FC' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'CD MirandÃƒÂ©s' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'red-700',
            'number' => 'black',
        ],
        'FC Andorra' => [
            'pattern' => 'solid',
            'primary' => 'blue-800',
            'secondary' => 'yellow-400',
            'number' => 'yellow-400',
        ],
        'Real Sociedad B' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // =============================================
        // England Ã¢â‚¬â€ Premier League (ENG1)
        // =============================================
        'Manchester City' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Liverpool FC' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'Arsenal FC' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Chelsea FC' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],
        'Manchester United' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Tottenham Hotspur' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-900',
            'number' => 'blue-900',
        ],
        'Newcastle United' => [
            'pattern' => 'stripes',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Aston Villa' => [
            'pattern' => 'solid',
            'primary' => 'purple-800',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],
        'West Ham United' => [
            'pattern' => 'solid',
            'primary' => 'red-800',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],
        'Nottingham Forest' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Everton FC' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Brighton & Hove Albion' => [
            'pattern' => 'stripes',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Crystal Palace' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],
        'Wolverhampton Wanderers' => [
            'pattern' => 'solid',
            'primary' => 'amber-500',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'Leeds United' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'white',
            'number' => 'blue-700',
        ],
        'Fulham FC' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'Brentford FC' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'AFC Bournemouth' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Sunderland AFC' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Burnley FC' => [
            'pattern' => 'solid',
            'primary' => 'red-800',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],

        // =============================================
        // Germany Ã¢â‚¬â€ Bundesliga (DEU1)
        // =============================================
        'Bayern Munich' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Borussia Dortmund' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'Bayer 04 Leverkusen' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'RB Leipzig' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Eintracht Frankfurt' => [
            'pattern' => 'solid',
            'primary' => 'black',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'VfB Stuttgart' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'SC Freiburg' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Borussia MÃƒÂ¶nchengladbach' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'green-600',
            'number' => 'green-600',
        ],
        'SV Werder Bremen' => [
            'pattern' => 'solid',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'VfL Wolfsburg' => [
            'pattern' => 'solid',
            'primary' => 'green-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        '1.FC KÃƒÂ¶ln' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Hamburger SV' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        '1.FC Union Berlin' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        '1.FSV Mainz 05' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'TSG 1899 Hoffenheim' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Augsburg' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'green-600',
        ],
        'FC St. Pauli' => [
            'pattern' => 'solid',
            'primary' => 'amber-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        '1.FC Heidenheim 1846' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],

        // =============================================
        // France Ã¢â‚¬â€ Ligue 1 (FRA1)
        // =============================================
        'Paris Saint-Germain' => [
            'pattern' => 'bar',
            'primary' => 'blue-900',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'Olympique Marseille' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],
        'AS Monaco' => [
            'pattern' => 'halves',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Olympique Lyon' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'LOSC Lille' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'blue-900',
            'number' => 'white',
        ],
        'OGC Nice' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Stade Rennais FC' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'RC Lens' => [
            'pattern' => 'stripes',
            'primary' => 'yellow-400',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'FC Nantes' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'green-600',
            'number' => 'green-600',
        ],
        'FC Toulouse' => [
            'pattern' => 'solid',
            'primary' => 'purple-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'RC Strasbourg Alsace' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Metz' => [
            'pattern' => 'solid',
            'primary' => 'red-800',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Le Havre AC' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'blue-900',
            'number' => 'white',
        ],
        'Stade Brestois 29' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'AJ Auxerre' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-600',
            'number' => 'blue-600',
        ],
        'Angers SCO' => [
            'pattern' => 'solid',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Paris FC' => [
            'pattern' => 'solid',
            'primary' => 'blue-800',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'FC Lorient' => [
            'pattern' => 'solid',
            'primary' => 'orange-500',
            'secondary' => 'black',
            'number' => 'black',
        ],

        // =============================================
        // Italy Ã¢â‚¬â€ Serie A (ITA1)
        // =============================================
        'Inter Milan' => [
            'pattern' => 'stripes',
            'primary' => 'blue-900',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Juventus FC' => [
            'pattern' => 'stripes',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'AC Milan' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'SSC Napoli' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'sky-400',
            'number' => 'white',
        ],
        'Atalanta BC' => [
            'pattern' => 'stripes',
            'primary' => 'blue-800',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'AS Roma' => [
            'pattern' => 'solid',
            'primary' => 'red-800',
            'secondary' => 'amber-500',
            'number' => 'amber-500',
        ],
        'SS Lazio' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'ACF Fiorentina' => [
            'pattern' => 'solid',
            'primary' => 'purple-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Bologna FC 1909' => [
            'pattern' => 'stripes',
            'primary' => 'red-700',
            'secondary' => 'blue-900',
            'number' => 'white',
        ],
        'Torino FC' => [
            'pattern' => 'solid',
            'primary' => 'red-800',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Genoa CFC' => [
            'pattern' => 'halves',
            'primary' => 'red-700',
            'secondary' => 'blue-900',
            'number' => 'white',
        ],
        'Udinese Calcio' => [
            'pattern' => 'stripes',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'US Lecce' => [
            'pattern' => 'stripes',
            'primary' => 'yellow-400',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Parma Calcio 1913' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'Cagliari Calcio' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'blue-800',
            'number' => 'white',
        ],
        'Hellas Verona' => [
            'pattern' => 'stripes',
            'primary' => 'yellow-400',
            'secondary' => 'blue-800',
            'number' => 'white',
        ],
        'US Sassuolo' => [
            'pattern' => 'stripes',
            'primary' => 'green-700',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Como 1907' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'US Cremonese' => [
            'pattern' => 'stripes',
            'primary' => 'red-700',
            'secondary' => 'gray-900',
            'number' => 'white',
        ],
        'Pisa Sporting Club' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'black',
            'number' => 'white',
        ],

        // =============================================
        // European transfer pool (EUR)
        // =============================================

        // Portugal
        'SL Benfica' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Porto' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Sporting CP' => [
            'pattern' => 'stripes',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'SC Braga' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Netherlands
        'Ajax Amsterdam' => [
            'pattern' => 'bar',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Feyenoord Rotterdam' => [
            'pattern' => 'halves',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'PSV Eindhoven' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Utrecht' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Go Ahead Eagles' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'yellow-400',
            'number' => 'yellow-400',
        ],

        // Turkey
        'Galatasaray' => [
            'pattern' => 'halves',
            'primary' => 'yellow-400',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Fenerbahce' => [
            'pattern' => 'stripes',
            'primary' => 'yellow-400',
            'secondary' => 'blue-900',
            'number' => 'blue-900',
        ],

        // Scotland
        'Celtic FC' => [
            'pattern' => 'hoops',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Rangers FC' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Austria
        'Red Bull Salzburg' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'SK Sturm Graz' => [
            'pattern' => 'stripes',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Belgium
        'Club Brugge KV' => [
            'pattern' => 'stripes',
            'primary' => 'blue-700',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'KRC Genk' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Union Saint-Gilloise' => [
            'pattern' => 'stripes',
            'primary' => 'yellow-400',
            'secondary' => 'blue-800',
            'number' => 'blue-800',
        ],

        // Greece
        'Olympiacos Piraeus' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'PAOK Thessaloniki' => [
            'pattern' => 'stripes',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Panathinaikos FC' => [
            'pattern' => 'solid',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Denmark
        'FC Copenhagen' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'FC Midtjylland' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],

        // Switzerland
        'FC Basel 1893' => [
            'pattern' => 'halves',
            'primary' => 'red-600',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],
        'BSC Young Boys' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'black',
            'number' => 'black',
        ],

        // Serbia
        'Red Star Belgrade' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Czech Republic
        'SK Slavia Prague' => [
            'pattern' => 'halves',
            'primary' => 'red-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Viktoria Plzen' => [
            'pattern' => 'stripes',
            'primary' => 'red-700',
            'secondary' => 'blue-800',
            'number' => 'white',
        ],

        // Hungary
        'FerencvÃƒÂ¡rosi TC' => [
            'pattern' => 'stripes',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Croatia
        'GNK Dinamo Zagreb' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Sweden
        'MalmÃƒÂ¶ FF' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Norway
        'FK BodÃƒÂ¸/Glimt' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'SK Brann' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Romania
        'FCSB' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],

        // Bulgaria
        'Ludogorets Razgrad' => [
            'pattern' => 'solid',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Israel
        'Maccabi Tel Aviv' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],

        // Azerbaijan
        'QarabaÃ„Å¸ FK' => [
            'pattern' => 'solid',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Cyprus
        'Pafos FC' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'yellow-400',
            'number' => 'yellow-400',
        ],

        // Kazakhstan
        'Kairat Almaty' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'black',
            'number' => 'black',
        ],

        // =============================================
        // Women's Club Aliases
        // =============================================
        'Arsenal Women' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Aston Villa Women' => [
            'pattern' => 'solid',
            'primary' => 'purple-800',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],
        'Brighton & Hove Albion Women' => [
            'pattern' => 'stripes',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Chelsea Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'blue-700',
            'number' => 'white',
        ],
        'Everton Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Leicester City Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Liverpool Women' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'London City Lionesses' => [
            'pattern' => 'solid',
            'primary' => 'amber-500',
            'secondary' => 'blue-900',
            'number' => 'blue-900',
        ],
        'Manchester City Women' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Manchester United Women' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Tottenham Hotspur Women' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-900',
            'number' => 'blue-900',
        ],
        'West Ham United Women' => [
            'pattern' => 'solid',
            'primary' => 'red-800',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],
        'Dijon FCO Women' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Fleury 91' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Le Havre Women' => [
            'pattern' => 'solid',
            'primary' => 'sky-500',
            'secondary' => 'blue-900',
            'number' => 'white',
        ],
        'RC Lens Women' => [
            'pattern' => 'stripes',
            'primary' => 'yellow-400',
            'secondary' => 'red-600',
            'number' => 'black',
        ],
        'OL Lyonnes' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'red-600',
        ],
        'Olympique de Marseille Women' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'sky-400',
            'number' => 'sky-400',
        ],
        'Montpellier HSC Women' => [
            'pattern' => 'solid',
            'primary' => 'orange-500',
            'secondary' => 'blue-800',
            'number' => 'blue-800',
        ],
        'FC Nantes Women' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'green-700',
            'number' => 'green-700',
        ],
        'Paris FC Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Paris Saint-Germain Women' => [
            'pattern' => 'bar',
            'primary' => 'blue-900',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'Racing Strasbourg Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-500',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'AS Saint-Etienne Women' => [
            'pattern' => 'solid',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Juventus Women' => [
            'pattern' => 'stripes',
            'primary' => 'black',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Inter Women' => [
            'pattern' => 'stripes',
            'primary' => 'blue-900',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'AC Milan Women' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Sassuolo Women' => [
            'pattern' => 'stripes',
            'primary' => 'green-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Parma Women' => [
            'pattern' => 'halves',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'Genoa Women' => [
            'pattern' => 'halves',
            'primary' => 'blue-900',
            'secondary' => 'red-700',
            'number' => 'white',
        ],
        'Fiorentina Women' => [
            'pattern' => 'solid',
            'primary' => 'purple-700',
            'secondary' => 'purple-700',
            'number' => 'white',
        ],
        'Ternana Women' => [
            'pattern' => 'stripes',
            'primary' => 'green-600',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'Lazio Women' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'AS Roma Women' => [
            'pattern' => 'solid',
            'primary' => 'red-900',
            'secondary' => 'amber-500',
            'number' => 'amber-500',
        ],
        'Napoli Women' => [
            'pattern' => 'solid',
            'primary' => 'sky-500',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Como Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Bayer 04 Leverkusen Women' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Bayern Munich Women' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Carl Zeiss Jena Women' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'FC Koln Women' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Eintracht Frankfurt Women' => [
            'pattern' => 'solid',
            'primary' => 'black',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'SGS Essen' => [
            'pattern' => 'solid',
            'primary' => 'purple-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'SC Freiburg Women' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'Hamburger SV Women' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],
        'Hoffenheim Women' => [
            'pattern' => 'solid',
            'primary' => 'blue-500',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'FC Nurnberg Women' => [
            'pattern' => 'solid',
            'primary' => 'red-900',
            'secondary' => 'black',
            'number' => 'white',
        ],
        'RB Leipzig Women' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'Union Berlin Women' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Werder Bremen Women' => [
            'pattern' => 'solid',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Wolfsburg Women' => [
            'pattern' => 'solid',
            'primary' => 'green-700',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // =============================================
        // National Teams Ã¢â‚¬â€ FIFA World Cup 2026
        // =============================================

        // Group A
        'Mexico' => [
            'pattern' => 'solid',
            'primary' => 'green-700',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'South Africa' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'green-700',
            'number' => 'green-700',
        ],
        'South Korea' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'black',
            'number' => 'white',
        ],

        // Group B
        'Canada' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Qatar' => [
            'pattern' => 'solid',
            'primary' => 'red-900',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Switzerland' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Group C
        'Brazil' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'green-600',
            'number' => 'green-700',
        ],
        'Morocco' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'green-600',
            'number' => 'white',
        ],
        'Haiti' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'Scotland' => [
            'pattern' => 'solid',
            'primary' => 'blue-900',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Group D
        'United States' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-900',
            'number' => 'blue-900',
        ],
        'Paraguay' => [
            'pattern' => 'stripes',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'blue-700',
        ],
        'Australia' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'green-700',
            'number' => 'green-700',
        ],

        // Group E
        'Germany' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],
        'CuraÃƒÂ§ao' => [
            'pattern' => 'solid',
            'primary' => 'blue-600',
            'secondary' => 'yellow-400',
            'number' => 'white',
        ],
        "Ivory Coast" => [
            'pattern' => 'solid',
            'primary' => 'orange-600',
            'secondary' => 'green-600',
            'number' => 'white',
        ],
        'Ecuador' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'blue-700',
            'number' => 'blue-700',
        ],

        // Group F
        'Netherlands' => [
            'pattern' => 'solid',
            'primary' => 'orange-500',
            'secondary' => 'white',
            'number' => 'black',
        ],
        'Japan' => [
            'pattern' => 'solid',
            'primary' => 'blue-800',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Tunisia' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],

        // Group G
        'Belgium' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'yellow-400',
            'number' => 'yellow-400',
        ],
        'Egypt' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Iran' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],
        'New Zealand' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'black',
            'number' => 'black',
        ],

        // Group H
        'Spain' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'yellow-400',
            'number' => 'yellow-400',
        ],
        'Cape Verde' => [
            'pattern' => 'solid',
            'primary' => 'blue-700',
            'secondary' => 'red-600',
            'number' => 'white',
        ],
        'Saudi Arabia' => [
            'pattern' => 'solid',
            'primary' => 'green-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Uruguay' => [
            'pattern' => 'solid',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'black',
        ],

        // Group I
        'France' => [
            'pattern' => 'solid',
            'primary' => 'blue-900',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Senegal' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'green-600',
            'number' => 'green-600',
        ],
        'Norway' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'blue-900',
        ],

        // Group J
        'Argentina' => [
            'pattern' => 'stripes',
            'primary' => 'sky-400',
            'secondary' => 'white',
            'number' => 'black',
        ],
        'Algeria' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'green-600',
            'number' => 'green-600',
        ],
        'Austria' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
        'Jordan' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'red-600',
            'number' => 'red-600',
        ],

        // Group K
        'Portugal' => [
            'pattern' => 'solid',
            'primary' => 'red-700',
            'secondary' => 'green-700',
            'number' => 'yellow-400',
        ],
        'Uzbekistan' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-600',
            'number' => 'blue-600',
        ],
        'Colombia' => [
            'pattern' => 'solid',
            'primary' => 'yellow-400',
            'secondary' => 'blue-800',
            'number' => 'blue-800',
        ],

        // Group L
        'England' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'blue-900',
            'number' => 'blue-900',
        ],
        'Croatia' => [
            'pattern' => 'hoops',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'blue-700',
        ],
        'Ghana' => [
            'pattern' => 'solid',
            'primary' => 'white',
            'secondary' => 'yellow-400',
            'number' => 'black',
        ],
        'Panama' => [
            'pattern' => 'solid',
            'primary' => 'red-600',
            'secondary' => 'white',
            'number' => 'white',
        ],
    ];

    private const DEFAULT_COLORS = [
        'pattern' => 'solid',
        'primary' => 'blue-600',
        'secondary' => 'white',
        'number' => 'white',
    ];

    /**
     * Get raw color config for a team (Tailwind names).
     * Used for DB storage.
     */
    public static function get(string $teamName): array
    {
        return self::TEAMS[$teamName] ?? self::DEFAULT_COLORS;
    }

    /**
     * Get all teams with hex colors for preview/testing.
     */
    public static function all(): array
    {
        $result = [];
        foreach (self::TEAMS as $name => $colors) {
            $result[$name] = self::toHex($colors);
        }

        return $result;
    }

    /**
     * Get color config with hex values for JavaScript rendering.
     */
    public static function toHex(array $colors): array
    {
        return [
            'pattern' => $colors['pattern'] ?? 'solid',
            'primary' => self::resolveHex($colors['primary'] ?? 'blue-600'),
            'secondary' => self::resolveHex($colors['secondary'] ?? 'white'),
            'number' => self::resolveHex($colors['number'] ?? 'white'),
        ];
    }

    /**
     * Convert a Tailwind color name to hex.
     */
    private static function resolveHex(string $color): string
    {
        return self::TAILWIND_HEX[$color] ?? '#6B7280';
    }
}

