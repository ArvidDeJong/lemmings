---
title: "Configuration"
nav_order: 4
description: "The settings of darvis/lemmings: LEMMINGS_ROUTE, LEMMINGS_URL and LEMMINGS_CLEAR_TOKEN, with their defaults, how to make a token and the effect of caching."
---

# Configuration

The package has three settings. You can leave all of them out.

| Key | `.env` | Default | What it does |
| --- | --- | --- | --- |
| `lemmings.clear_token` | `LEMMINGS_CLEAR_TOKEN` | none | The secret that opens `/clearDgP`. Without it that route answers 404 |
| `lemmings.route` | `LEMMINGS_ROUTE` | `/lemmings` | The path of the easter egg page |
| `lemmings.url` | `LEMMINGS_URL` | `https://lemmings.darvis.nl` | The address the umbrella in the picture links to |

```php
// config/lemmings.php
return [
    'clear_token' => env('LEMMINGS_CLEAR_TOKEN'),
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
- The path of the second route, `/clearDgP`, is fixed.

## The link

```env
LEMMINGS_URL=https://your-own-site.example
```

The address is escaped when it is written into the page. An empty value counts as not set. The package does not check that it is a web address, so only put a value there that you trust: whatever you configure becomes the `href` of the link.

## The clear token

`GET /clearDgP` clears the caches and recreates the storage link, for hosting without shell access. It only works for a request that carries this secret; every other request gets a 404.

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

## What is not configurable

There is no setting for middleware, for a domain, for the path of `/clearDgP` or its limit of five wrong tokens a minute, or for switching the easter egg page off. [Quick start](quick-start.md) shows how to replace a route from your own routes file.

## Reading the settings in code

Inside the package, `Darvis\Lemmings\Support\LemmingsConfig` is the one class that reads the config: `LemmingsConfig::route()`, `LemmingsConfig::url()` and `LemmingsConfig::clearToken()`. The first two return the default when the value is missing or empty; `clearToken()` returns `null` when the value is missing, empty or not a string.
