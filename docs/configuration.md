---
title: "Configuration"
nav_order: 4
description: "The settings of darvis/lemmings: LEMMINGS_ROUTE, LEMMINGS_URL, LEMMINGS_CLEAR_TOKEN, LEMMINGS_LAYOUT and the package list, with their defaults, how to make a token and the effect of caching."
---

# Configuration

The package has five settings. You can leave all of them out.

| Key | `.env` | Default | What it does |
| --- | --- | --- | --- |
| `lemmings.clear_token` | `LEMMINGS_CLEAR_TOKEN` | none | The secret that opens `/clearDgP` and the packages page. Without it both answer 404 |
| `lemmings.layout` | `LEMMINGS_LAYOUT` | none | The layout component the packages page renders in. Without it the package uses its own plain layout |
| `lemmings.packages` | | a few `darvis/*` packages, `laravel-lang/common`, `laravel/ai`, `laravel/boost` and `sentry/sentry-laravel` | The packages the packages page lists |
| `lemmings.route` | `LEMMINGS_ROUTE` | `/lemmings` | The path of the easter egg page; the packages page is on that path plus `/packages` |
| `lemmings.url` | `LEMMINGS_URL` | `https://lemmings.darvis.nl` | The address the umbrella in the picture links to |

```php
// config/lemmings.php
return [
    'clear_token' => env('LEMMINGS_CLEAR_TOKEN'),
    'layout' => env('LEMMINGS_LAYOUT'),
    'packages' => [
        'darvis/mailtrap' => [
            'name' => 'Mailtrap',
            'purpose' => 'Validates recipients before sending, logs every outgoing mail, ...',
            'url' => 'https://packagist.org/packages/darvis/mailtrap',
        ],
        // ...
    ],
    'route' => env('LEMMINGS_ROUTE', '/lemmings'),
    'url' => env('LEMMINGS_URL', 'https://lemmings.darvis.nl'),
];
```

## The path

```env
LEMMINGS_ROUTE=/built-by
```

- The page moves to `/built-by` and `/lemmings` answers with a 404. The route name stays `lemmings`.
- The path is read once, while the application boots. When you use `php artisan route:cache`, build the cache again after you change it.
- An empty value counts as not set, so the page stays on `/lemmings`. It never becomes the home page by accident.
- The packages page moves along: `/built-by/packages`. Its route name stays `lemmings.packages`.
- The path of the maintenance route, `/clearDgP`, is fixed.

## The link

```env
LEMMINGS_URL=https://your-own-site.example
```

The address is escaped when it is written into the page. An empty value counts as not set. The package does not check that it is a web address, so only put a value there that you trust: whatever you configure becomes the `href` of the link.

## The clear token

`GET /clearDgP` clears the caches and recreates the storage link, for hosting without shell access. It only works for a request that carries this secret; every other request gets a 404. The same secret opens the [packages page](#the-packages).

```bash
php artisan lemmings:install
```

The command writes a fresh token of 48 hex characters to `.env` and shows it once. It keeps a token that is already there unless you pass `--force`, and `--url=https://your-own-site.example` also writes `LEMMINGS_URL`. By hand:

```bash
php -r "echo bin2hex(random_bytes(24));"
```

```env
LEMMINGS_CLEAR_TOKEN=paste-the-48-characters-here
```

```bash
curl -H "X-Lemmings-Token: paste-the-48-characters-here" https://your-site.example/clearDgP
```

- No value, or an empty value, means no token: the route answers 404 for everybody.
- The token is read from the `X-Lemmings-Token` header, and without that header from `?token=`. The query string works in a browser, but it ends up in the access log of the web server.
- The value is read from the config on every request. When you use `php artisan config:cache`, build the cache again after you change it.
- A `config/lemmings.php` you published before 1.7.0 has no `clear_token` key. You don't have to add it: Laravel fills a missing key from the config file of the package, so `LEMMINGS_CLEAR_TOKEN` in `.env` is enough. A `clear_token` key that is in your published file wins, so don't set it to a fixed value there.

[Security and privacy](security.md) says what the token protects and what to do when it leaks.

## The packages

`GET /lemmings/packages` lists the packages in `lemmings.packages` and shows for each one whether Composer installed it in this application, with the version. The list is keyed by the Composer name and every entry has three strings:

```php
'packages' => [
    'spatie/laravel-backup' => [
        'name' => 'Laravel Backup',
        'purpose' => 'Backs up the database and the files to a disk of your choice.',
        'url' => 'https://github.com/spatie/laravel-backup',
    ],
],
```

- Publish the config file to change the list. Your published `packages` array replaces the one of the package, so keep the entries you still want.
- The page shows the entries in the order of the array.
- An entry that misses `name`, `purpose` or `url`, or whose value is not a string, is left out.
- The page needs the clear token, in the header or as `?token=`, and answers 404 without it. Wrong tokens count towards the same limit of five a minute as `/clearDgP`.

## The layout

```env
LEMMINGS_LAYOUT=layouts.app
```

The packages page renders inside a Blade layout component. The value is the component name: `layouts.app` is `resources/views/components/layouts/app.blade.php`, the layout of the Livewire starter kit. The page is plain HTML with its own small stylesheet, so it looks the same in your layout and in the one of the package.

- Without a value the package uses its own layout, `darvis-lemmings::layout`: a plain page with a light background, the list and a `robots` meta tag with `noindex, nofollow`.
- The package route has no middleware. A layout that needs the session, for example one with a Livewire component or `@auth`, only works when you register the route yourself with the `web` middleware; see [Quick start](quick-start.md#put-a-login-in-front-of-the-page) for the pattern.
- A component that does not exist gives Laravel's "Unable to locate a class or view for component" error, not a fallback.

## What is not configurable

There is no setting for middleware, for a domain, for the path of `/clearDgP` or the `/packages` suffix, for the limit of five wrong tokens a minute, or for switching the easter egg page off. [Quick start](quick-start.md) shows how to replace a route from your own routes file.

## Reading the settings in code

Inside the package, `Darvis\Lemmings\Support\LemmingsConfig` is the one class that reads the config: `LemmingsConfig::route()`, `LemmingsConfig::packagesRoute()`, `LemmingsConfig::url()`, `LemmingsConfig::layout()`, `LemmingsConfig::packages()` and `LemmingsConfig::clearToken()`. The first four return the default when the value is missing or empty; `packages()` returns only the complete entries; `clearToken()` returns `null` when the value is missing, empty or not a string. `Darvis\Lemmings\Support\Packages::status()` returns the list joined with what Composer installed.
