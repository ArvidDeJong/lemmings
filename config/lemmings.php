<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Lemmings URL
    |--------------------------------------------------------------------------
    |
    | The URL that the lemmings easter egg links to. This is typically the
    | developer's portfolio or company website to prove ownership.
    |
    */

    'url' => env('LEMMINGS_URL', 'https://lemmings.darvis.nl'),

    /*
    |--------------------------------------------------------------------------
    | Route Path
    |--------------------------------------------------------------------------
    |
    | The path where the lemmings easter egg will be accessible.
    | Default: /lemmings
    |
    */

    'route' => env('LEMMINGS_ROUTE', '/lemmings'),

];
