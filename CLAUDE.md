# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository. The conventions shared by every darvis package (language, releases, CI, docs site, Boost guidelines, public API policy) are in [../CLAUDE.md](../CLAUDE.md); this file only holds what is specific to this package.

## Package overview

`darvis/lemmings` is a Laravel package (PHP 8.2+, Laravel 11/12/13) that adds a hidden Lemmings easter egg page to a host app. The umbrella in the picture links to the developer's site, to prove who built the application.

- Namespace: `Darvis\Lemmings\` → `src/`
- Service provider `Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider`, auto-registered via `extra.laravel.providers` in [composer.json](composer.json)
- Config key: `lemmings`

## Architecture

- The code lives under `src/Laravel/` (provider, routes, views), not in the layout of the other packages. Don't move it: host apps and their cached package manifest reference the provider by its class name.
- [LemmingsConfig](src/Support/LemmingsConfig.php) is the only place that reads the package config; don't call `config('lemmings.…')` elsewhere, the view included. `tests/Feature/ConfigAccessorTest.php` walks `src/` and `resources/` for it. An empty `lemmings.route` falls back to `/lemmings`, because `Route::get('')` would make the easter egg the home page of the host app.
- [routes/web.php](src/Laravel/routes/web.php) registers two GET routes with `loadRoutesFrom()`, so outside every middleware group: `lemmings` on the configured path and `lemmings.clear` on the fixed path `/clearDgP`, which runs seven Artisan cache commands for whoever requests it. The path is read while the provider boots, so tests that need another path set it in `getEnvironmentSetUp()` (`tests/CustomPathTestCase.php`).
- [lemmings.blade.php](src/Laravel/resources/views/lemmings.blade.php) is a static page of about 68 KB, almost all of it one base64 GIF. Don't print it or grep through it without cutting the lines. The only dynamic value is the escaped `LemmingsConfig::url()`.
- There is no config switch and no middleware setting. A host app replaces a route by defining one on the same path, because its routes are registered later and the last one wins. The tests pin that, since the docs promise it.

## Conventions

- Keep the public API compatible within 1.x: the list is in `CONTRIBUTING.md`. It includes both route names, the view name and the `src/Laravel/` layout.
- The text and the design of the easter egg page are the product. Don't "fix" the HTML 4.01 markup, the title or the picture; a site owner sees every change. The docs site footer credit rule (ARVID.NL, no personal name) is about `docs/`, not about this page.
- Nothing from the request may reach the view, and the view shows nothing about the host app (versions, environment, debug state, paths). The page is public on every site that has the package, so either one is an XSS or an information leak on all of them at once. `tests/Feature/LemmingsRouteTest.php` checks both.
- Don't remove, move or protect `/clearDgP` on your own. It is an open maintenance route, and the docs say so plainly, but closing it changes what the owner of every host app can do; that is a decision for a minor or major release with a CHANGELOG entry, not a drive-by fix.
- Never call the real `/clearDgP` closure with the real Artisan kernel in a test: it clears the Testbench caches and writes a storage link into `vendor/`. `tests/Feature/ClearRouteTest.php` swaps the `Artisan` facade for a stand-in (Testbench's kernel is final, so `shouldReceive()` does not work).
- The license file is `LICENSE.md`. Don't rename it: a case or extension rename gains nothing, and `_config.yml`, the README and the badges point at it.
