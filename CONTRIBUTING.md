# Contributing

Contributions are welcome: bug reports, fixes, documentation and ideas.

## Before you start

- **Bugs:** open an [issue](https://github.com/ArvidDeJong/lemmings/issues/new/choose) with the steps to reproduce.
- **Features:** open an issue first. This package stays tiny on purpose, so let's agree a feature fits before you build it.
- **Security issues:** don't open an issue; see [SECURITY.md](SECURITY.md).

## Development

```bash
git clone https://github.com/ArvidDeJong/lemmings.git
cd lemmings
composer install

composer test      # Pest
composer lint      # Pint, check only (composer format fixes)
composer analyse   # Larastan, level 8
```

CI runs the tests on PHP 8.2 to 8.4 with Laravel 11, 12 and 13, on the lowest and the latest dependencies.

## Pull requests

- Add or update tests for every change in behaviour. The route tests are HTTP tests on Testbench; never run `vendor:publish` or a real Artisan cache command from a test, they write into `vendor/`.
- Keep the public API compatible within 1.x: the provider class `Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider` and the `src/Laravel/` layout, the route names `lemmings` and `lemmings.clear`, the view name `darvis-lemmings::lemmings`, the config keys `lemmings.route` and `lemmings.url` with their defaults, the env names `LEMMINGS_ROUTE` and `LEMMINGS_URL`, and the publish tag `lemmings-config`.
- The text and the design of the easter egg page are the product. Don't change them in a patch or a minor release.
- Nothing from the request may reach the page, and the page shows nothing about the application (versions, environment, debug state, paths). `tests/Feature/LemmingsRouteTest.php` checks both.
- Read settings through `Support\LemmingsConfig`, never with `config('lemmings.…')`.
- Write code, comments and messages in English.
- Update `docs/`, `CHANGELOG.md` (under `Unreleased`) and `resources/boost/` when users will notice the change.
- The documentation in `docs/` is also the website. Don't write `{{ }}` or `{% %}` there outside a raw block; Jekyll would render it.

## Code of conduct

This project follows the [Contributor Covenant](CODE_OF_CONDUCT.md).
