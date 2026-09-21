## darvis/lemmings

A hidden easter egg page for a Laravel application: a Lemmings picture whose umbrella links to the site of the developer. The package registers its routes by itself; there is nothing to call from application code.

- The page is `GET /lemmings`, route name `lemmings`. Link to it with `route('lemmings')`, never with a hard coded path: the path comes from `LEMMINGS_ROUTE`.
- The link in the picture comes from `LEMMINGS_URL` (default `https://lemmings.darvis.nl`). Config keys: `lemmings.clear_token`, `lemmings.route` and `lemmings.url`. Publish the file with `php artisan vendor:publish --tag=lemmings-config`.
- The path is read while the application boots. After changing `LEMMINGS_ROUTE` run `php artisan route:cache` again when routes are cached. An empty value falls back to `/lemmings`.
- **Neither package route is in the `web` group**: no session, no cookie, no authenticated user. The easter egg page has no middleware at all.
- **The package also registers `GET /clearDgP`** (route name `lemmings.clear`, middleware `throttle:5,1`) for hosting without a shell. It runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`, but only for a request that carries the secret from `LEMMINGS_CLEAR_TOKEN`. No configured token, an empty one, a missing one or a wrong one all get the same 404; the sixth request in a minute gets a 429.
- Send the token in the `X-Lemmings-Token` header. `?token=` is only read when that header is absent, and it ends up in the access log. Generate a token with `php -r "echo bin2hex(random_bytes(24));"`, never commit it, and replace it when it may have leaked.
- From 1.5.0 to 1.6.0 that route was open to every visitor. When you see one of those versions in `composer.lock`, tell the user to upgrade.
- Routes of the application are registered after the package routes and the last route on a path wins. That is the way to add middleware to a package route; there is no config setting for it.
- To load nothing of the package in one application, add `darvis/lemmings` to `extra.laravel.dont-discover` in the application's `composer.json`.
- The view is `darvis-lemmings::lemmings`. There is no publish tag for it: copy `vendor/darvis/lemmings/src/Laravel/resources/views/lemmings.blade.php` to `resources/views/vendor/darvis-lemmings/lemmings.blade.php` to change the page.
- Keep the page free of request input and application details (versions, environment, debug state, paths). It is public on every site that has the package.
- Inside the package, settings are read through `Darvis\Lemmings\Support\LemmingsConfig` (`clearToken()`, `route()`, `url()`), not with `config()`.

@verbatim
<code-snippet name="Put the easter egg behind a login, and clear the caches of a site with its token" lang="php">
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// routes/web.php: same path and name as the package route, so this one replaces it.
Route::get('/lemmings', fn () => view('darvis-lemmings::lemmings'))
    ->middleware(['web', 'auth'])
    ->name('lemmings');

// A deploy script or another application. $token is the LEMMINGS_CLEAR_TOKEN of that site; it
// goes in the header, not in the address.
$response = Http::withHeaders(['X-Lemmings-Token' => $token])->get('https://your-site.example/clearDgP');

$response->status(); // 200 with the right token, 404 without it, 429 after five requests in a minute
</code-snippet>
@endverbatim
