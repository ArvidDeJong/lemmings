<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Clear Token
    |--------------------------------------------------------------------------
    |
    | The secret that opens GET /clearDgP, the page that clears the caches and
    | recreates the storage link on hosting without a shell, and the packages
    | page. Without a token neither page exists. Use a long random value, for
    | example the output of: php -r "echo bin2hex(random_bytes(24));"
    |
    */

    'clear_token' => env('LEMMINGS_CLEAR_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The Blade component the packages page renders in, for example
    | "layouts.app" for resources/views/components/layouts/app.blade.php.
    | Without a value the package uses its own plain layout.
    |
    */

    'layout' => env('LEMMINGS_LAYOUT'),

    /*
    |--------------------------------------------------------------------------
    | Packages
    |--------------------------------------------------------------------------
    |
    | The packages the packages page lists, keyed by their Composer name. For
    | each one the page shows the name as a link, the purpose, and whether
    | Composer installed it in this application, with the version.
    |
    */

    'packages' => [
        'darvis/laravel-api-key-alert' => [
            'name' => 'Laravel API Key Alert',
            'purpose' => 'Mails you the moment an API key stops working, and reminds you daily while APP_DEBUG or APP_ENV is wrong on a server.',
            'url' => 'https://packagist.org/packages/darvis/laravel-api-key-alert',
        ],
        'darvis/livewire-honeypot' => [
            'name' => 'Livewire Honeypot',
            'purpose' => 'Honeypot and time trap spam protection for Livewire and Laravel forms, without a CAPTCHA.',
            'url' => 'https://packagist.org/packages/darvis/livewire-honeypot',
        ],
        'darvis/mailtrap' => [
            'name' => 'Mailtrap',
            'purpose' => 'Validates recipients before sending, logs every outgoing mail, processes signed Mailtrap webhooks and ships an inbox page.',
            'url' => 'https://packagist.org/packages/darvis/mailtrap',
        ],
        'laravel-lang/common' => [
            'name' => 'Laravel Lang',
            'purpose' => 'Translations of the messages Laravel, its validation rules and the starter kits ship, in many languages, kept up to date with one command.',
            'url' => 'https://packagist.org/packages/laravel-lang/common',
        ],
        'laravel/ai' => [
            'name' => 'Laravel AI SDK',
            'purpose' => 'One API for OpenAI, Anthropic, Gemini and other providers: agents with tools, structured output, embeddings, images and audio.',
            'url' => 'https://laravel.com/docs/ai-sdk',
        ],
        'laravel/boost' => [
            'name' => 'Laravel Boost',
            'purpose' => 'Gives an AI coding assistant an MCP server, guidelines and skills for the Laravel packages in this application.',
            'url' => 'https://laravel.com/docs/boost',
        ],
        'sentry/sentry-laravel' => [
            'name' => 'Sentry',
            'purpose' => 'Reports exceptions and performance data of the application to Sentry.',
            'url' => 'https://packagist.org/packages/sentry/sentry-laravel',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Path
    |--------------------------------------------------------------------------
    |
    | The path where the lemmings easter egg will be accessible. The packages
    | page lives under it, on <route>/packages.
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
