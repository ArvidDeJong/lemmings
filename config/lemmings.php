<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Clear Token
    |--------------------------------------------------------------------------
    |
    | The secret that opens GET /clearDgP, the page that clears the caches and
    | recreates the storage link on hosting without a shell. Without a token
    | the page does not exist. Use a long random value, for example the output
    | of: php -r "echo bin2hex(random_bytes(24));"
    |
    */

    'clear_token' => env('LEMMINGS_CLEAR_TOKEN'),

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

];
