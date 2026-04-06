<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External References
    |--------------------------------------------------------------------------
    |
    | Standings references used to calibrate team-level valuation multipliers.
    | Primary target requested: Flashscore.
    |
    */
    'sources' => [
        'ESP1' => 'https://www.flashscore.com/football/spain/liga-f-women/standings/',
        'ESP2' => 'https://www.flashscore.com/football/spain/primera-federacion-women/standings/',
        'ENG1' => 'https://www.flashscore.com/football/england/wsl/standings/',
        'DEU1' => 'https://www.flashscore.com/football/germany/bundesliga-women/standings/',
        'FRA1' => 'https://www.flashscore.com/football/france/premiere-ligue-women/standings/',
        'ITA1' => 'https://www.flashscore.com/football/italy/serie-a-women/standings/',
    ],

    /*
    |--------------------------------------------------------------------------
    | League Multipliers
    |--------------------------------------------------------------------------
    |
    | Values are applied over player market values before converting to abilities.
    | 1.00 = neutral. >1 boosts valuation, <1 reduces it.
    |
    | Keys are normalized team names:
    | - lowercase
    | - accents removed
    | - non-alphanumeric collapsed to single spaces
    |
    */
    'competitions' => [
        // Liga F
        'ESP1' => [
            'team_multipliers' => [
                'fc barcelona' => 1.22,
                'real madrid cf' => 1.14,
                'atletico de madrid' => 1.10,
                'athletic club' => 1.06,
                'real sociedad' => 1.04,
                'levante ud' => 1.03,
                'cd tenerife femenino' => 1.01,
                'sevilla fc' => 1.00,
                'granada cf' => 0.99,
                'madrid cff' => 0.98,
                'sd eibar' => 0.97,
                'rcd espanyol' => 0.96,
                'deportivo la coruna' => 0.95,
                'fc badalona women' => 0.94,
                'alhama cf' => 0.93,
                'dux logrono' => 0.92,
            ],
        ],

        // 1RFEF (modelled in project as ESP2)
        'ESP2' => [
            'team_multipliers' => [
                'valencia feminas cf' => 1.05,
                'real oviedo' => 1.03,
                'fc barcelona b' => 1.02,
                'sdf real betis balompie' => 1.01,
                'villarreal cf' => 1.00,
                'deportivo alaves' => 0.99,
                'atletico de madrid b' => 0.98,
                'cdf osasuna femenino' => 0.98,
                'ce europa' => 0.97,
                'se aem' => 0.96,
                'cd alba ff' => 0.96,
                'real madrid cf b' => 0.95,
                'cacereno femenino' => 0.94,
                'cd tenerife femenino b' => 0.93,
            ],
        ],

        // Women's Super League
        'ENG1' => [
            'team_multipliers' => [
                'chelsea fc' => 1.15,
                'arsenal fc' => 1.12,
                'manchester city' => 1.10,
                'manchester united' => 1.08,
                'liverpool fc' => 1.03,
                'tottenham hotspur' => 1.01,
                'brighton hove albion' => 1.00,
                'aston villa' => 0.99,
                'everton fc' => 0.98,
                'west ham united' => 0.96,
                'leicester city' => 0.95,
                'london city lionesses' => 0.94,
            ],
        ],

        // Frauen-Bundesliga
        'DEU1' => [
            'team_multipliers' => [
                'fc bayern munchen' => 1.14,
                'vfl wolfsburg' => 1.12,
                'eintracht frankfurt' => 1.08,
                'bayer 04 leverkusen' => 1.04,
                'tsg 1899 hoffenheim' => 1.03,
                'sc freiburg' => 1.01,
                'rb leipzig' => 1.00,
                'sv werder bremen' => 0.99,
                'sgs essen' => 0.97,
                '1 fc union berlin' => 0.96,
                'hamburger sv' => 0.95,
                '1 fc koln' => 0.94,
                '1 fc nurnberg' => 0.93,
                'fc carl zeiss jena' => 0.92,
            ],
        ],

        // Premiere Ligue
        'FRA1' => [
            'team_multipliers' => [
                'ol lyonnes' => 1.20,
                'paris saint germain' => 1.13,
                'paris fc' => 1.08,
                'dijon fco' => 1.02,
                'fc fleury 91' => 1.01,
                'montpellier hsc' => 1.00,
                'olympique de marseille' => 0.99,
                'rc strasbourg' => 0.98,
                'fc nantes' => 0.97,
                'le havre ac' => 0.96,
                'as saint etienne' => 0.95,
                'rc lens' => 0.94,
            ],
        ],

        // Serie A Women
        'ITA1' => [
            'team_multipliers' => [
                'juventus fc' => 1.13,
                'as roma' => 1.11,
                'inter milano' => 1.08,
                'acf fiorentina' => 1.04,
                'ac milan' => 1.03,
                'us sassuolo' => 1.00,
                'lazio women' => 0.99,
                'fc como women' => 0.98,
                'parma calcio' => 0.97,
                'napoli women' => 0.96,
                'genoa cfc' => 0.95,
                'ternana women' => 0.94,
            ],
        ],
    ],
];

