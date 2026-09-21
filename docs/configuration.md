---
title: Configuration
nav_order: 3
description: "The two settings of darvis/lemmings, LEMMINGS_ROUTE and LEMMINGS_URL, with their defaults and the effect of route caching."
---

# Configuration

The package has two settings. Both have a default, so you can leave them out.

| Key | `.env` | Default | What it does |
| --- | --- | --- | --- |
| `lemmings.route` | `LEMMINGS_ROUTE` | `/lemmings` | The path of the easter egg page |
| `lemmings.url` | `LEMMINGS_URL` | `https://lemmings.darvis.nl` | The address the umbrella in the picture links to |

```php
// config/lemmings.php
return [
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
- The path of the second route, `/clearDgP`, is fixed. See [Security and privacy](security.md).

## The link

```env
LEMMINGS_URL=https://your-own-site.example
```

The address is escaped when it is written into the page. An empty value counts as not set. The package does not check that it is a web address, so only put a value there that you trust: whatever you configure becomes the `href` of the link.

## What is not configurable

There is no setting for middleware, for a domain or for switching the routes off. [How it works](how-it-works.md) shows how to replace a route from your own routes file.

## Reading the settings in code

Inside the package, `Darvis\Lemmings\Support\LemmingsConfig` is the one class that reads the config: `LemmingsConfig::route()` and `LemmingsConfig::url()`. Both return the default when the value is missing or empty.
