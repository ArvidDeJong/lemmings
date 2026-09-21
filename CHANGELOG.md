# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.7.1] - 2026-09-21

### Security
- **A refused request to `GET /clearDgP` no longer shows that the route is there.** In 1.7.0 the
  route had the `throttle:5,1` middleware, which put `X-RateLimit-Limit` and `X-RateLimit-Remaining`
  headers on every answer, the 404 of a refusal included, and answered the sixth request within a
  minute with a 429. A 404 for a path that does not exist has neither, so one request told a
  stranger that the route exists. Every refusal is now the 404 of an unknown path, with the same
  headers: no configured token, a missing token, a wrong token and too many wrong tokens. The
  route counts the wrong tokens itself, five a minute for each IP address, and while an address is
  over the limit the token is not looked at. Nothing to do. The path is still in the public source
  of the package and other methods than GET still answer 405, so keep treating the token, not the
  path, as the secret.

### Changed
- `GET /clearDgP` never answers 429 any more, and sends no `X-RateLimit-*` or `Retry-After` header.
  After five wrong or missing tokens from one IP address within a minute, every request from that
  address gets a 404 until that minute is over, a request with the right token included. A script
  that waited for a 429 has to treat a 404 as "wait a minute and try again".
- A request with the right token no longer counts towards the limit. In 1.7.0 the sixth call within
  a minute was refused even with the right token.
- The route has no middleware again. `php artisan route:list` shows none for `lemmings.clear`.

### Added
- Documentation pages: a quick start (your link, a hidden link to the page, a login in front of it,
  your own view), a testing page with a complete test for a host application, and a troubleshooting
  page organised by symptom. The installation page has numbered steps and a "Check that it works"
  section with the `curl -i` calls for `/clearDgP` and the status codes to expect.
- A Laravel Boost section in the README, which now follows the section order of the other darvis
  packages.

### Fixed
- The security page said the site "keeps working, but slower" after the route and config caches are
  cleared. It now says what happens: Laravel reads the route and config files on every request
  until they are cached again.
- The docs now say that `"status": "success"` from `/clearDgP` means the seven commands were called
  and none threw an exception. The route does not look at what a command reports, so `storage:link`
  saying that the link already exists still ends in `success`.

## [1.7.0] - 2026-09-21

### Security
- **`GET /clearDgP` was open to every visitor from 1.5.0 to 1.6.0.** The route, which the changelog
  of 1.5.0 did not mention, ran `cache:clear`, `route:clear`, `config:clear`, `view:clear`,
  `storage:link`, `event:clear` and `optimize:clear` for anyone who requested it, on a path that is
  the same on every site and is in the public source. A visitor could reset the rate limiters kept
  in the cache, such as the login throttle, and undo the route and config cache of a deploy, as often
  as they liked. The route now only works with the secret from `LEMMINGS_CLEAR_TOKEN`, sent in the
  `X-Lemmings-Token` header or as `?token=`, and it is throttled to five requests a minute. Without
  a configured token, and for a missing or wrong one, it answers with a plain 404.

  What to do:
  - You never used the page: nothing. It is closed after the upgrade.
  - You use the page: set `LEMMINGS_CLEAR_TOKEN` in `.env` to a long random value, for example the
    output of `php -r "echo bin2hex(random_bytes(24));"`, and call the page with the token:
    `curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP`. Prefer the header;
    a token in the address ends up in the access log.
  - You closed the page yourself with a `Route::get('/clearDgP', ...)` in your own routes: you can
    remove that line.
  - You published `config/lemmings.php` before this release: the file has no `clear_token` key, and
    it does not need one. Laravel fills a missing key from the config file of the package, so the
    variable in `.env` is enough. Run `php artisan config:cache` again when you cache the config.

### Added
- The config key `lemmings.clear_token`, read from `LEMMINGS_CLEAR_TOKEN`, without a default. It is
  the secret that opens `GET /clearDgP`; an empty value counts as no token.
- `Darvis\Lemmings\Support\LemmingsConfig`, the one place that reads the package config, with
  `clearToken()`, `route()` and `url()`. A test fails the build on a direct `config('lemmings.…')`
  read.
- A test suite (Pest on Testbench). The package had none. It covers both routes, the token check
  and the throttle, the view, the config, the publish tag, and that the page shows nothing from the
  request and nothing about the application.
- A documentation site at https://arviddejong.github.io/lemmings/ with an FAQ and an `llms.txt`,
  and a Laravel Boost guideline and skill in `resources/boost/`.
- The tooling of the other darvis packages: Pint, Larastan level 8, the `test`, `lint`, `format`
  and `analyse` composer scripts, CI on PHP 8.2 to 8.4 with Laravel 11, 12 and 13, issue forms, a
  code of conduct and a `.gitattributes` that keeps development files out of the dist archive.

### Changed
- `GET /clearDgP` answers 404 until `LEMMINGS_CLEAR_TOKEN` is set, and after that for every request
  without the right token. With the right token the answer is the same JSON as before. The route
  has the `throttle:5,1` middleware: the sixth request within a minute gets a 429, which also counts
  for requests with the right token. The path and the route name `lemmings.clear` are unchanged. A
  deploy script or a bookmark that calls the page has to send the token from now on.
- An empty `LEMMINGS_ROUTE` or `LEMMINGS_URL` now counts as not set and falls back to `/lemmings`
  and `https://lemmings.darvis.nl`. Before, an empty route registered the easter egg as the home
  page of the application, and an empty url made the picture link to the page itself. Nothing to
  do unless you relied on that.
- The keys in `config/lemmings.php` are in alphabetical order (`clear_token`, `route`, `url`). The
  existing keys, env names and defaults are unchanged; a published config file keeps working as it
  is.

## [1.6.0] - 2026-03-18

### Changed

-   Extended Laravel compatibility to Laravel 13 (`^11.0|^12.0|^13.0`)

## [1.5.0] - 2026-01-26

### Added

-   Configuration file for customizable URL and route path
-   Config publishing via `php artisan vendor:publish --tag=lemmings-config`
-   Named route `lemmings` for easier referencing
-   Environment variable support (`LEMMINGS_URL`, `LEMMINGS_ROUTE`)

### Changed

-   Documentation translated to English
-   Improved ServiceProvider with config merging

## [1.4.1] - 2025-11-05

### Removed

-   Version field removed from composer.json for better package management

### Changed

-   Improved code formatting in composer.json

## [1.3.0] - 2025-06-26

### Removed

-   Tests removed to improve compatibility
-   Laravel Pint removed to minimize dependencies

## [1.2.0] - 2025-06-26

### Changed

-   Extended Laravel compatibility to Laravel 11 and 12 (`^11.0|^12.0`)

## [1.1.0] - 2024-XX-XX

### Added

-   [Previous changes for version 1.1.0]

## [1.0.1] - 2025-03-06

### Added

-   Support for Laravel 11
-   Compatibility with Livewire 3
-   FluxUI integration

### Changed

-   Minimum PHP version raised to 8.2
-   PHPUnit upgrade to version 11
-   Improved documentation

### Removed

-   Support for Laravel < 11

## [1.0.0] - 2024-01-01

### Added

-   Initial release
-   Basic Lemmings easter egg functionality
-   Laravel auto-discovery support
