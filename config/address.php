<?php

return [
    'publication' => [
        'path'  => database_path('seeders/publication/PSGC-1Q-2026-Publication-Datafile.xlsx'),
        'sheet' => 4,
    ],

    'models' => [
        'region'    => \App\Models\Region::class,
        'province'  => \App\Models\Province::class,
        'city'      => \App\Models\City::class,
        'barangay'  => \App\Models\Barangay::class,
    ],
];