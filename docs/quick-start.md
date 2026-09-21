---
title: "Quick start"
nav_order: 3
description: "Use darvis/lemmings in a Laravel application: set your link, add a hidden link to the page, put a login in front of it and replace the picture with your view."
---

# Quick start

After `composer require darvis/lemmings` the page is on `/lemmings`. This page shows the four things people do next.

## Point the umbrella at your site

`.env`:

```env
LEMMINGS_URL=https://your-own-site.example
```

The umbrella in the picture now opens your site in a new tab. Run `php artisan config:cache` again when you cache the config.

## Add a hidden link to the page

The package adds no link anywhere. When you want one, use the route name, because the path can be changed with `LEMMINGS_ROUTE`.

`resources/views/components/footer.blade.php` (or any other Blade view):

{% raw %}
```blade
<a href="{{ route('lemmings') }}" rel="nofollow" aria-hidden="true" tabindex="-1">&pi;</a>
```
{% endraw %}

This prints a small π that leads to the easter egg. `rel="nofollow"` asks search engines not to follow it.

## Put a login in front of the page

The package route has no middleware (code that runs before the route, such as the login check). A route of your own on the same path replaces it, because the routes of your application are registered after those of the package and the last route on a path wins.

`routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::get('/lemmings', fn () => view('darvis-lemmings::lemmings'))
    ->middleware(['web', 'auth'])
    ->name('lemmings');
```

A visitor who is not logged in is now sent to the route named `login` of your application. Keep the path and the name the same as the package route: the path from `LEMMINGS_ROUTE`, the name `lemmings`. The same name on another path makes `php artisan route:cache` fail, see [Troubleshooting](troubleshooting.md#route-cache-fails-on-the-name-lemmings).

## Use your own page

There is no publish tag for the view. Copy the file, and Laravel uses your copy instead of the one in the package:

```bash
mkdir -p resources/views/vendor/darvis-lemmings
cp vendor/darvis/lemmings/src/Laravel/resources/views/lemmings.blade.php \
   resources/views/vendor/darvis-lemmings/lemmings.blade.php
```

`resources/views/vendor/darvis-lemmings/lemmings.blade.php`, the link in your own version:

{% raw %}
```blade
<a href="{{ \Darvis\Lemmings\Support\LemmingsConfig::url() }}" target="_blank" rel="noopener">Built by us</a>
```
{% endraw %}

`LemmingsConfig::url()` returns `LEMMINGS_URL`, so the setting keeps working. Use the escaping Blade echo as above, never the unescaped one. The package route is not in the `web` group, so the session, `@auth` and `@csrf` do not work in this view unless you also replace the route as shown above.

## Clear the caches without a shell

Set `LEMMINGS_CLEAR_TOKEN` as described in [Installation](installation.md), then:

```bash
curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP
```

The route runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`. In a browser you can use `https://your-site.example/clearDgP?token=your-token`, but then the token ends up in the access log of the web server. Read [Security and privacy](security.md) first.
