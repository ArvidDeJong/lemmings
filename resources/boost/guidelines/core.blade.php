## darvis/lemmings

A hidden easter egg page for a Laravel application: a Lemmings picture whose umbrella links to the site of the developer. The package registers its routes by itself; there is nothing to call from application code.

- The page is `GET /lemmings`, route name `lemmings`. Link to it with `route('lemmings')`, never with a hard coded path: the path comes from `LEMMINGS_ROUTE`.
- The link in the picture comes from `LEMMINGS_URL` (default `https://lemmings.darvis.nl`). Config keys: `lemmings.route` and `lemmings.url`. Publish the file with `php artisan vendor:publish --tag=lemmings-config`.
- The path is read while the application boots. After changing `LEMMINGS_ROUTE` run `php artisan route:cache` again when routes are cached. An empty value falls back to `/lemmings`.
- **Both package routes have no middleware**, not even `web`: no session, no cookie, no authenticated user, no rate limit.
- **The package also registers `GET /clearDgP`** (route name `lemmings.clear`). Anyone who requests it runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`. Tell the user about it when you review routes or security, and close it as in the snippet below unless they use it on purpose.
- Routes of the application are registered after the package routes and the last route on a path wins. That is the way to add middleware to a package route or to close one; there is no config switch for it.
- To load nothing of the package in one application, add `darvis/lemmings` to `extra.laravel.dont-discover` in the application's `composer.json`.
- The view is `darvis-lemmings::lemmings`. There is no publish tag for it: copy `vendor/darvis/lemmings/src/Laravel/resources/views/lemmings.blade.php` to `resources/views/vendor/darvis-lemmings/lemmings.blade.php` to change the page.
- Keep the page free of request input and application details (versions, environment, debug state, paths). It is public on every site that has the package.
- Inside the package, settings are read through `Darvis\Lemmings\Support\LemmingsConfig` (`route()`, `url()`), not with `config()`.

@verbatim
<code-snippet name="Put the easter egg behind a login and close the maintenance route, in routes/web.php" lang="php">
use Illuminate\Support\Facades\Route;

// Same path and name as the package route, so this one replaces it.
Route::get('/lemmings', fn () => view('darvis-lemmings::lemmings'))
    ->middleware(['web', 'auth'])
    ->name('lemmings');

// The package route runs cache:clear and six other commands for every visitor.
Route::get('/clearDgP', fn () => abort(404));
</code-snippet>
@endverbatim
