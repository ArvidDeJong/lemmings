---
name: lemmings-development
description: Work with darvis/lemmings. Use it to set the path and the link of the Lemmings easter egg page, replace the page with your own view, put it behind middleware, clear the caches without a shell through the token protected /clearDgP route, and test those routes.
---

# darvis/lemmings development

## When to use this skill

Use this skill when an application has `darvis/lemmings` installed and the task touches the easter egg page, the routes of the application, a security review, route caching, clearing caches on hosting without a shell, or a 404 on `/lemmings` or `/clearDgP`.

## How it runs

1. Laravel discovers `Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider`.
2. `register()` merges `config/lemmings.php` under the key `lemmings`.
3. `boot()` loads the routes file, registers the view namespace `darvis-lemmings` and offers the config file under the publish tag `lemmings-config`.
4. The routes file registers two GET routes without middleware, so outside the `web` group:

| Name | Path | Middleware | What it does |
| --- | --- | --- | --- |
| `lemmings` | `LEMMINGS_ROUTE`, default `/lemmings` | none | Returns the view `darvis-lemmings::lemmings` |
| `lemmings.clear` | `/clearDgP`, fixed | none | With the right token: runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear`, `optimize:clear` and returns JSON. Otherwise 404 |

A request to `lemmings.clear` is checked in this order. Without a configured token: `abort(404)`. Then `RateLimiter::tooManyAttempts('lemmings-clear:'.$request->ip(), 5)`: `abort(404)` without looking at the token. Then the token, from the `X-Lemmings-Token` header and only without that header from `?token=`, is compared with `LemmingsConfig::clearToken()` using `hash_equals()`. A missing or wrong one is counted with `RateLimiter::hit($key, 60)` and ends in `abort(404)`. The right one is not counted. Every refusal is the 404 of a path that does not exist: no 403, no 429, no rate limit headers.

The view is static: a title, a black background, one embedded picture of 700 by 600 pixels and an image map whose one area (the umbrella) opens `LEMMINGS_URL` in a new tab. It carries `noindex, nofollow`. It has no script and no form, and nothing from the request is written into it.

## When something is off

| What you see | Cause | What to do |
| --- | --- | --- |
| `/lemmings` gives a 404 | `LEMMINGS_ROUTE` is set to another path | Use `route('lemmings')`, or check `php artisan route:list --name=lemmings` |
| The new path gives a 404, the old one still works | Routes are cached; the path is read while the application boots | `php artisan route:cache` again |
| The umbrella links to `lemmings.darvis.nl` | `LEMMINGS_URL` is not set, or empty | Set it in `.env`, then `php artisan config:cache` when config is cached |
| `auth()->user()` is null on the page, or `session()` fails in an overridden view | The package route has no `web` middleware | Define your own route on the same path with `['web']` |
| `/clearDgP` gives a 404 | No `LEMMINGS_CLEAR_TOKEN`, an empty one, a wrong token, or a wrong `X-Lemmings-Token` header next to a right `?token=` (the header wins) | Set the token, send it in the header; `php artisan config:cache` again when config is cached |
| `/clearDgP` gives a 404 with the right token, after failed tries | Five wrong or missing tokens from that IP address within a minute; the token is not looked at until the minute is over. Behind a proxy the application does not trust, every visitor shares one address | Wait a minute and send the right token once. Configure trusted proxies |
| Caches are empty or the login throttle resets without a deploy | The package is 1.5.0 to 1.6.0, where `/clearDgP` was open, or the token leaked | Upgrade to 1.7.0 or later; put a new value in `LEMMINGS_CLEAR_TOKEN` |
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

### Use the maintenance route

The route is closed until a token is set.

```bash
php -r "echo bin2hex(random_bytes(24));"
```

```env
LEMMINGS_CLEAR_TOKEN=the-generated-value
```

```bash
curl -H "X-Lemmings-Token: the-generated-value" https://your-site.example/clearDgP
```

The answer is `{"status":"success","message":"All caches have been cleared and storage link recreated."}`. `https://your-site.example/clearDgP?token=the-generated-value` works in a browser, but the token then lands in the access log of the web server and in the browser history. Remove the variable to close the route again.

A `config/lemmings.php` published before 1.7.0 has no `clear_token` key. That is fine: `mergeConfigFrom()` fills the missing key from the package config, so the env variable is enough.

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

- The path `/clearDgP` is the same on every site with this package and is in the public source. Only the token is a secret. Whoever has it can empty the default cache store, which can hold rate limiter counters and locks, and remove the cached routes and config.
- Never put the token in a link, a view, JavaScript, a log line or the repository. Prefer the header over `?token=`. Replace the token when it may have leaked.
- Don't replace the 404 with a 403, a 429 or a message, and don't put the `throttle` middleware on an override of this route: it adds `X-RateLimit-*` headers to every answer, which shows a stranger the route is there. 1.7.0 did that.
- Don't describe the route as invisible. The path is in the public source, and `POST /clearDgP` answers 405 where an unknown path gives 404. The token protects it, not the path.
- An empty `LEMMINGS_CLEAR_TOKEN` is no token. It does not make `?token=` with an empty value work.
- There is no config switch for the easter egg page, no middleware setting and no setting for the limit of five wrong tokens. Don't invent `lemmings.enabled` or `lemmings.middleware`; they do nothing.
- The package routes are outside the `web` group. A view override that uses the session, `@auth` or `@csrf` does not work on the package route.
- `LEMMINGS_URL` is escaped but not validated. Whatever is configured becomes the `href`.
- Never echo request input or application details (versions, environment, debug state, paths) in an overridden view. The page is public.
- Don't hard code `/lemmings` in links; use `route('lemmings')`.

## Settings

| Key | `.env` | Default |
| --- | --- | --- |
| `lemmings.clear_token` | `LEMMINGS_CLEAR_TOKEN` | none, the maintenance route is closed |
| `lemmings.route` | `LEMMINGS_ROUTE` | `/lemmings` |
| `lemmings.url` | `LEMMINGS_URL` | `https://lemmings.darvis.nl` |

Inside the package they are read through `Darvis\Lemmings\Support\LemmingsConfig`: `route()` and `url()` return the default for a missing or empty value, `clearToken()` returns `null` for a missing, empty or non string value.

## Testing

```php
it('shows the easter egg', function () {
    $this->get(route('lemmings'))
        ->assertOk()
        ->assertViewIs('darvis-lemmings::lemmings');
});

it('keeps the maintenance route closed without the token', function () {
    config(['lemmings.clear_token' => 'test-token']);

    $this->get('/clearDgP')->assertNotFound();
    $this->get('/clearDgP?token=wrong')->assertNotFound();
});
```

- Assert the 404. Don't send the right token in a test of a host app: the route then really clears the caches of the application under test and writes the storage link.
- The token is read on every request, so `config(['lemmings.clear_token' => ...])` inside a test works. A missing or wrong token counts towards the limit of five a minute for `127.0.0.1`. With the `array` cache store every test starts at zero; otherwise call `RateLimiter::clear('lemmings-clear:127.0.0.1')`.
- To test another path, set `lemmings.route` before the application boots (in `getEnvironmentSetUp()` on Testbench, or in `phpunit.xml` with `LEMMINGS_ROUTE`); changing the config inside a test is too late.
