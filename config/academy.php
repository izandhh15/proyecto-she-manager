<?php

return [
    /*
    |----------------------------------------------------------------------
    | Reserve-team poaching map
    |----------------------------------------------------------------------
    |
    | When a reserve player's contract expires without a promotion, nearby
    | reserve sides can try to sign her. The keys are first-team names and
    | the values are nearby clubs whose reserve side should get priority.
    |
    */
    'reserve_poaching_targets' => [
        'Valencia Féminas CF' => [
            'Levante UD',
            'Villarreal CF',
        ],
        'FC Barcelona' => [
            'RCD Espanyol',
            'Levante UD',
            'Athletic Club',
        ],
        'Real Sociedad' => [
            'Athletic Club',
            'SD Eibar',
            'Deportivo Alavés',
        ],
        'Atlético de Madrid' => [
            'Madrid CFF',
            'Fundación Rayo Vallecano',
        ],
        'CD Tenerife Femenino' => [
            'Granada CF',
            'Deportivo La Coruña',
        ],
    ],

    'reserve_poach_probability' => [
        'base' => 35,
        'age_21_or_less_bonus' => 15,
        'high_potential_bonus' => 15,
        'high_ability_bonus' => 10,
    ],
];
