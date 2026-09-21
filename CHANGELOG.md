# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added
- `Darvis\Lemmings\Support\LemmingsConfig`, the one place that reads the package config, with
  `route()` and `url()`. A test fails the build on a direct `config('lemmings.…')` read.
- A test suite (Pest on Testbench). The package had none. It covers both routes, the view, the
  config, the publish tag, and that the page shows nothing from the request and nothing about the
  application.
- A documentation site at https://arviddejong.github.io/lemmings/ with an FAQ and an `llms.txt`,
  and a Laravel Boost guideline and skill in `resources/boost/`.
- The tooling of the other darvis packages: Pint, Larastan level 8, the `test`, `lint`, `format`
  and `analyse` composer scripts, CI on PHP 8.2 to 8.4 with Laravel 11, 12 and 13, issue forms, a
  code of conduct and a `.gitattributes` that keeps development files out of the dist archive.

### Changed
- An empty `LEMMINGS_ROUTE` or `LEMMINGS_URL` now counts as not set and falls back to `/lemmings`
  and `https://lemmings.darvis.nl`. Before, an empty route registered the easter egg as the home
  page of the application, and an empty url made the picture link to the page itself. Nothing to
  do unless you relied on that.
- The keys in `config/lemmings.php` are in alphabetical order (`route`, `url`). Keys, env names and
  defaults are unchanged; a published config file keeps working as it is.

### Security
- The documentation now states that the package registers `GET /clearDgP` (route name
  `lemmings.clear`) without middleware, and that it runs `cache:clear`, `route:clear`,
  `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear` for anyone who
  requests it. The route has been there since 1.5.0, where the changelog did not mention it, and its
  behaviour has not changed in this release.
  Close it in your application when you don't use it: define `Route::get('/clearDgP', fn () => abort(404));`
  in `routes/web.php`, or block the path in the web server. See the security notes on the
  documentation site.

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
