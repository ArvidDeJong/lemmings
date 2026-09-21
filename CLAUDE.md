# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository. The conventions shared by every darvis package (language, releases, CI, docs site, Boost guidelines, public API policy) are in [../CLAUDE.md](../CLAUDE.md); this file only holds what is specific to this package.

## Package overview

`darvis/lemmings` is a Laravel package (PHP 8.2+, Laravel 11/12/13) that adds a hidden Lemmings easter egg page to a host app. The umbrella in the picture links to the developer's site, to prove who built the application. It also registers a token protected maintenance route that clears the caches on hosting without a shell.

- Namespace: `Darvis\Lemmings\` → `src/`
- Service provider `Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider`, auto-registered via `extra.laravel.providers` in [composer.json](composer.json)
- Config key: `lemmings`

## Architecture

- The code lives under `src/Laravel/` (provider, routes, views), not in the layout of the other packages. Don't move it: host apps and their cached package manifest reference the provider by its class name.
- [LemmingsConfig](src/Support/LemmingsConfig.php) is the only place that reads the package config; don't call `config('lemmings.…')` elsewhere, the view included. `tests/Feature/ConfigAccessorTest.php` walks `src/` and `resources/` for it. An empty `lemmings.route` falls back to `/lemmings`, because `Route::get('')` would make the easter egg the home page of the host app.
- [routes/web.php](src/Laravel/routes/web.php) registers two GET routes with `loadRoutesFrom()`, so outside the `web` group: `lemmings` on the configured path, without middleware, and `lemmings.clear` on the fixed path `/clearDgP`, also without middleware. The second one runs seven Artisan cache commands, but only when the `X-Lemmings-Token` header, or without that header `?token=`, equals `LemmingsConfig::clearToken()`. The easter egg path is read while the provider boots, so tests that need another path set it in `getEnvironmentSetUp()` (`tests/CustomPathTestCase.php`).
- [lemmings.blade.php](src/Laravel/resources/views/lemmings.blade.php) is a static page of about 68 KB, almost all of it one base64 GIF. Don't print it or grep through it without cutting the lines. The only dynamic value is the escaped `LemmingsConfig::url()`.
- A config file published before 1.7.0 has no `clear_token` key. `mergeConfigFrom()` merges the top level keys, so the key comes from the package config and `LEMMINGS_CLEAR_TOKEN` works without touching the published file. `tests/Feature/PublishedConfigTest.php` pins that, because the CHANGELOG promises it.
- There is no config switch for the easter egg page and no middleware setting. A host app replaces a route by defining one on the same path, because its routes are registered later and the last one wins. The tests pin that, since the docs promise it.

## Conventions

- Keep the public API compatible within 1.x: the list is in `CONTRIBUTING.md`. It includes both route names, the view name and the `src/Laravel/` layout.
- The text and the design of the easter egg page are the product. Don't "fix" the HTML 4.01 markup, the title or the picture; a site owner sees every change. The docs site footer credit rule (ARVID.NL, no personal name) is about `docs/`, not about this page.
- Nothing from the request may reach the view, and the view shows nothing about the host app (versions, environment, debug state, paths). The page is public on every site that has the package, so either one is an XSS or an information leak on all of them at once. `tests/Feature/LemmingsRouteTest.php` checks both.
- `/clearDgP` must stay behind `LemmingsConfig::clearToken()`, and never be open by default again. From 1.5.0 to 1.6.0 it had no check at all: every visitor of every host app could empty the cache, which resets rate limiters such as the login throttle, and undo the route and config cache of a deploy. A default token, a fallback value or an "open in local" shortcut brings that back on the sites that never set anything.
- `clearToken()` returns `null` for an empty or non string value, and the route refuses when it is `null`. An empty secret would equal an empty `?token=` and open the route to everyone.
- Compare the token with `hash_equals()`, never with `===` or `==`. A normal comparison stops at the first different byte, so the response time tells an attacker how much of a guess was right.
- Every refusal is the same `abort(404)`, and nothing else: no configured token, no token given, a wrong one, an array in `?token[]=`, and too many wrong ones. A 403, a 429 or a message tells a stranger that the route is there, whether a token is configured or that a guess was wrong. `tests/Feature/ClearRouteTest.php` compares the refusal with the answer for an unknown path, status and headers.
- Never put the `throttle` middleware back on this route. 1.7.0 had `throttle:5,1`: it answers 429 on the sixth request and puts `X-RateLimit-Limit` and `X-RateLimit-Remaining` on every response, the 404 included, so the first request already showed the route exists. The limit is counted inside the closure with `RateLimiter` for that reason: key `lemmings-clear:<ip>`, five wrong tokens, 60 seconds.
- Keep the order in the closure: no configured token, then `tooManyAttempts()`, then the comparison. While an address is over the limit the token is not looked at, so guessing stays at five a minute; a right token is never counted, so the owner cannot lock themselves out by using the page.
- Don't claim in the docs that the route is invisible. `POST /clearDgP` answers 405 with `Allow: GET, HEAD` and `OPTIONS` answers 200, where an unknown path gives 404; the response time differs; and the path is in the public source. The token protects the route, not the path.
- Never log the token or the request address of this route, and never echo the token in a response or an exception message. `?token=` puts the secret in the URL, which is why the header is read first and the docs recommend it.
- The path `/clearDgP`, the route name `lemmings.clear`, the header name, the `token` parameter and the JSON answer are public API: deploy scripts of host apps call them.
- Never let a test reach the real Artisan kernel through `/clearDgP` with a valid token: it clears the Testbench caches and writes a storage link into `vendor/`. `tests/Feature/ClearRouteTest.php` swaps the `Artisan` facade for a stand-in (Testbench's kernel is final, so `shouldReceive()` does not work).
- The license file is `LICENSE.md`. Don't rename it: a case or extension rename gains nothing, and `_config.yml`, the README and the badges point at it.
