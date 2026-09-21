---
name: lemmings-development
description: Work with darvis/lemmings. Use it to set the path and the link of the Lemmings easter egg page, replace the page with your own view, put it behind middleware, close the /clearDgP maintenance route the package registers, and test those routes.
---

# darvis/lemmings development

## When to use this skill

Use this skill when an application has `darvis/lemmings` installed and the task touches the easter egg page, the routes of the application, a security review, route caching, or a 404 on `/lemmings`.

## How it runs

1. Laravel discovers `Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider`.
2. `register()` merges `config/lemmings.php` under the key `lemmings`.
3. `boot()` loads the routes file, registers the view namespace `darvis-lemmings` and offers the config file under the publish tag `lemmings-config`.
4. The routes file registers two GET routes, outside every middleware group:

| Name | Path | What it does |
| --- | --- | --- |
| `lemmings` | `LEMMINGS_ROUTE`, default `/lemmings` | Returns the view `darvis-lemmings::lemmings` |
| `lemmings.clear` | `/clearDgP`, fixed | Runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear`, `optimize:clear` and returns JSON |

The view is static: a title, a black background, one embedded picture of 700 by 600 pixels and an image map whose one area (the umbrella) opens `LEMMINGS_URL` in a new tab. It carries `noindex, nofollow`. It has no script and no form, and nothing from the request is written into it.

## When something is off

| What you see | Cause | What to do |
| --- | --- | --- |
| `/lemmings` gives a 404 | `LEMMINGS_ROUTE` is set to another path | Use `route('lemmings')`, or check `php artisan route:list --name=lemmings` |
| The new path gives a 404, the old one still works | Routes are cached; the path is read while the application boots | `php artisan route:cache` again |
| The umbrella links to `lemmings.darvis.nl` | `LEMMINGS_URL` is not set, or empty | Set it in `.env`, then `php artisan config:cache` when config is cached |
| `auth()->user()` is null on the page, or `session()` fails in an overridden view | The package route has no `web` middleware | Define your own route on the same path with `['web']` |
| Caches are empty or the login throttle resets without a deploy | Somebody requests `/clearDgP` | Close the route, see below |
| `Route [lemmings] not defined` | The package is in `dont-discover`, or not installed | Remove the link or load the provider |

## Scenarios

### Change the path and the link

```env
LEMMINGS_ROUTE=/built-by
LEMMINGS_URL=https://your-own-site.example
```

The route name stays `lemmings`. An empty value counts as not set.

### Put the page behind a login

Application routes are registered after the package routes, and the last route on a path wins:

```php
Route::get('/lemmings', fn () => view('darvis-lemmings::lemmings'))
    ->middleware(['web', 'auth'])
    ->name('lemmings');
```

### Close the maintenance route

```php
Route::get('/clearDgP', fn () => abort(404));
```

Or block the path in the web server. Verify with `curl -i https://your-site.example/clearDgP`.

### Use your own page

Copy `vendor/darvis/lemmings/src/Laravel/resources/views/lemmings.blade.php` to `resources/views/vendor/darvis-lemmings/lemmings.blade.php` and edit the copy. There is no publish tag for the view. Keep the link on the setting, with the escaping echo:

```blade
<a href="{{ \Darvis\Lemmings\Support\LemmingsConfig::url() }}" target="_blank" rel="noopener">Built by us</a>
```

### Leave the package out of one application

```json
"extra": { "laravel": { "dont-discover": ["darvis/lemmings"] } }
```

Both routes are gone after `composer dump-autoload`.

## Pitfalls

- `/clearDgP` is public, has the same path on every site with this package and is in the public source. It is not a secret. `cache:clear` empties the default cache store, which can hold rate limiter counters and locks.
- There is no config switch for the routes and no middleware setting. Don't invent `lemmings.enabled` or `lemmings.middleware`; they do nothing.
- The package routes are outside the `web` group. A view override that uses the session, `@auth` or `@csrf` does not work on the package route.
- `LEMMINGS_URL` is escaped but not validated. Whatever is configured becomes the `href`.
- Never echo request input or application details (versions, environment, debug state, paths) in an overridden view. The page is public.
- Don't hard code `/lemmings` in links; use `route('lemmings')`.

## Settings

| Key | `.env` | Default |
| --- | --- | --- |
| `lemmings.route` | `LEMMINGS_ROUTE` | `/lemmings` |
| `lemmings.url` | `LEMMINGS_URL` | `https://lemmings.darvis.nl` |

Inside the package they are read through `Darvis\Lemmings\Support\LemmingsConfig::route()` and `::url()`; both return the default for a missing or empty value.

## Testing

```php
it('shows the easter egg', function () {
    $this->get(route('lemmings'))
        ->assertOk()
        ->assertViewIs('darvis-lemmings::lemmings');
});

it('has closed the maintenance route', function () {
    $this->get('/clearDgP')->assertNotFound();
});
```

- Never request the open `/clearDgP` in a test: it really clears the caches of the application under test. Test that it is closed.
- To test another path, set `lemmings.route` before the application boots (in `getEnvironmentSetUp()` on Testbench, or in `phpunit.xml` with `LEMMINGS_ROUTE`); changing the config inside a test is too late.
