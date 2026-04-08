<?php

return [
    /*
    |--------------------------------------------------------------------------
    | National Team Fallback Stadium Pools
    |--------------------------------------------------------------------------
    |
    | Most national teams derive their rotating home venues from club stadiums
    | already seeded for the same country. These fallback pools cover countries
    | that are present in tournament mode but do not have a domestic club pool
    | in the current reference dataset.
    |
    */
    'club_overrides' => [
        '163' => [
            'name' => 'Volksparkstadion',
            'capacity' => 57000,
        ],
        'esp2-fc-barcelona-b' => [
            'name' => 'Estadi Johan Cruyff',
            'capacity' => 6000,
        ],
    ],

    'national_team_pools' => [
        'us' => [
            ['name' => 'Audi Field', 'capacity' => 20000],
            ['name' => 'Lower.com Field', 'capacity' => 20621],
            ['name' => 'Snapdragon Stadium', 'capacity' => 35000],
            ['name' => 'GEODIS Park', 'capacity' => 30000],
            ['name' => 'Subaru Park', 'capacity' => 18500],
        ],
        'mx' => [
            ['name' => 'Estadio Azteca', 'capacity' => 87523],
            ['name' => 'Estadio Universitario', 'capacity' => 41886],
            ['name' => 'Estadio Hidalgo', 'capacity' => 30000],
            ['name' => 'Estadio Nemesio Diez', 'capacity' => 30000],
            ['name' => 'Estadio BBVA', 'capacity' => 53500],
        ],
        'br' => [
            ['name' => 'Neo Quimica Arena', 'capacity' => 49205],
            ['name' => 'Arena Fonte Nova', 'capacity' => 47907],
            ['name' => 'Estadio Nacional Mane Garrincha', 'capacity' => 72800],
            ['name' => 'Beira-Rio', 'capacity' => 50000],
            ['name' => 'Arena Pernambuco', 'capacity' => 46000],
        ],
        'ar' => [
            ['name' => 'Estadio Mario Alberto Kempes', 'capacity' => 57000],
            ['name' => 'Estadio Malvinas Argentinas', 'capacity' => 42000],
            ['name' => 'Estadio Libertadores de America', 'capacity' => 42069],
            ['name' => 'Estadio Unico Madre de Ciudades', 'capacity' => 30000],
            ['name' => 'Estadio Ciudad de Caseros', 'capacity' => 16000],
        ],
        'pt' => [
            ['name' => 'Estadio do Dragao', 'capacity' => 50033],
            ['name' => 'Estadio Municipal de Leiria', 'capacity' => 23888],
            ['name' => 'Estadio Cidade de Coimbra', 'capacity' => 30210],
            ['name' => 'Estadio do Bessa', 'capacity' => 28263],
            ['name' => 'Estadio Municipal de Aveiro', 'capacity' => 30438],
        ],
        'nl' => [
            ['name' => 'Philips Stadion', 'capacity' => 35119],
            ['name' => 'De Grolsch Veste', 'capacity' => 30205],
            ['name' => 'Galgenwaard', 'capacity' => 23750],
            ['name' => 'Rat Verlegh Stadion', 'capacity' => 19000],
            ['name' => 'Abe Lenstra Stadion', 'capacity' => 26100],
        ],
        'au' => [
            ['name' => 'Stadium Australia', 'capacity' => 83000],
            ['name' => 'Suncorp Stadium', 'capacity' => 52500],
            ['name' => 'AAMI Park', 'capacity' => 30050],
            ['name' => 'McDonald Jones Stadium', 'capacity' => 33000],
            ['name' => 'HBF Park', 'capacity' => 20500],
        ],
        'jp' => [
            ['name' => 'Japan National Stadium', 'capacity' => 68000],
            ['name' => 'Panasonic Stadium Suita', 'capacity' => 39694],
            ['name' => 'Noevir Stadium Kobe', 'capacity' => 30000],
            ['name' => 'Yurtec Stadium Sendai', 'capacity' => 19694],
            ['name' => 'Edion Peace Wing Hiroshima', 'capacity' => 28520],
        ],
        'se' => [
            ['name' => 'Strawberry Arena', 'capacity' => 50000],
            ['name' => 'Gamla Ullevi', 'capacity' => 18000],
            ['name' => 'Tele2 Arena', 'capacity' => 30000],
            ['name' => 'Eleda Stadion', 'capacity' => 22000],
            ['name' => 'PlatinumCars Arena', 'capacity' => 17000],
        ],
        'no' => [
            ['name' => 'Ullevaal Stadion', 'capacity' => 28000],
            ['name' => 'Lerkendal Stadion', 'capacity' => 21000],
            ['name' => 'Brann Stadion', 'capacity' => 17400],
            ['name' => 'Intility Arena', 'capacity' => 16555],
            ['name' => 'Arasen Stadion', 'capacity' => 12000],
        ],
        'ch' => [
            ['name' => 'St. Jakob-Park', 'capacity' => 38512],
            ['name' => 'Stade de Geneve', 'capacity' => 30084],
            ['name' => 'Stadion Letzigrund', 'capacity' => 26000],
            ['name' => 'Stockhorn Arena', 'capacity' => 10000],
            ['name' => 'Stade de la Tuiliere', 'capacity' => 12544],
        ],
        'fi' => [
            ['name' => 'Helsinki Olympic Stadium', 'capacity' => 36200],
            ['name' => 'Tammelan Stadion', 'capacity' => 8000],
            ['name' => 'Veritas Stadion', 'capacity' => 9372],
            ['name' => 'OmaSP Stadion', 'capacity' => 6000],
            ['name' => 'Kuopion Keskuskentta', 'capacity' => 5000],
        ],
        'is' => [
            ['name' => 'Laugardalsvollur', 'capacity' => 9800],
            ['name' => 'Kaplakrikavollur', 'capacity' => 6450],
            ['name' => 'Akranesvollur', 'capacity' => 5800],
            ['name' => 'Kopavogsvollur', 'capacity' => 3500],
            ['name' => 'Vikingsvollur', 'capacity' => 2000],
        ],
    ],
];
