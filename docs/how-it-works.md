---
title: How it works
nav_order: 4
description: "The two routes and the view behind darvis/lemmings, the token check on the maintenance route, and how to replace the page or add middleware."
---

# How it works

The service provider does three things: it merges `config/lemmings.php`, it loads one routes file and it registers the view namespace `darvis-lemmings`.

## The routes

| Name | Method | Path | Middleware | Answer |
| --- | --- | --- | --- | --- |
| `lemmings` | GET | `LEMMINGS_ROUTE`, default `/lemmings` | none | The view `darvis-lemmings::lemmings` |
| `lemmings.clear` | GET | `/clearDgP` | `throttle:5,1` | With the right token: JSON, after clearing the caches. Otherwise 404 |

Neither route is in the `web` group. The easter egg page therefore starts no session, sets no cookie and does not know who is logged in.

## The maintenance route

`lemmings.clear` is meant for hosting without shell access. A request goes through these steps:

1. `throttle:5,1` allows five requests a minute for each visitor. The sixth gets a 429.
2. The token is taken from the `X-Lemmings-Token` header. Only when the request has no such header, it is taken from `?token=`.
3. The token is compared with `LEMMINGS_CLEAR_TOKEN`. No configured token, an empty one, a missing one or a wrong one all end in the same 404.
4. With the right token the route runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`, and answers with:

```json
{"status": "success", "message": "All caches have been cleared and storage link recreated."}
```

```bash
curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP
```

See [Configuration](configuration.md#the-clear-token) for the token and [Security and privacy](security.md) for what it protects.

## The page

The view is one static HTML file:

- the title `Oh no more Lemmings....` and a black background;
- one picture of 700 by 600 pixels, embedded in the page itself, so there is no second request and no asset to publish;
- an image map with one clickable area, the umbrella, that opens `LEMMINGS_URL` in a new tab;
- a `robots` meta tag with `noindex, nofollow`.

It has no script, no stylesheet and no form.

## Your own page

There is no publish tag for the view. Copy the file and Laravel uses your copy:

```bash
mkdir -p resources/views/vendor/darvis-lemmings
cp vendor/darvis/lemmings/src/Laravel/resources/views/lemmings.blade.php \
   resources/views/vendor/darvis-lemmings/lemmings.blade.php
```

Keep the link in your copy connected to the setting:

{% raw %}
```blade
<a href="{{ \Darvis\Lemmings\Support\LemmingsConfig::url() }}" target="_blank" rel="noopener">Built by us</a>
```
{% endraw %}

Use the escaping Blade echo as above, never the unescaped one.

## Replacing a route

The routes of your application are registered after the routes of the package, and the last route on a path wins. So a route in your own `routes/web.php` replaces the one from the package:

```php
// The easter egg, but only for people who are logged in.
Route::get('/lemmings', fn () => view('darvis-lemmings::lemmings'))
    ->middleware(['web', 'auth'])
    ->name('lemmings');
```

Use the same path as `LEMMINGS_ROUTE` when you changed it. The same works for `/clearDgP`, for example to put it behind your own login instead of the token.

## Leaving the package out of one application

To keep the package installed but load nothing of it, tell Laravel not to discover it, in the `composer.json` of your application:

```json
"extra": {
    "laravel": {
        "dont-discover": ["darvis/lemmings"]
    }
}
```

Run `composer dump-autoload` afterwards. Both routes are gone.
