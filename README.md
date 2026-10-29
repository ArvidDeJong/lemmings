# Lemmings

[![Latest version](https://img.shields.io/packagist/v/darvis/lemmings.svg)](https://packagist.org/packages/darvis/lemmings)
[![Tests](https://github.com/ArvidDeJong/lemmings/actions/workflows/tests.yml/badge.svg)](https://github.com/ArvidDeJong/lemmings/actions/workflows/tests.yml)
[![PHP version](https://img.shields.io/packagist/dependency-v/darvis/lemmings/php.svg)](https://packagist.org/packages/darvis/lemmings)
[![License](https://img.shields.io/packagist/l/darvis/lemmings.svg)](LICENSE.md)

A hidden Lemmings easter egg page for Laravel applications. The umbrella in the picture links to your own site, as a quiet proof of who built the application. The package also adds a page that clears the caches on hosting without shell access, which only works with a secret token.

## Features

- **Nothing to set up** - install the package and `/lemmings` is there
- **Your path, your link** - `LEMMINGS_ROUTE` and `LEMMINGS_URL` in `.env`
- **Says nothing about the application** - no versions, no environment, nothing from the request on the page
- **Asks search engines to stay away** - the page carries `noindex, nofollow`
- **Your own page** - override the view in `resources/views/vendor/darvis-lemmings`
- **Clear the caches without a shell** - `/clearDgP`, closed until you set `LEMMINGS_CLEAR_TOKEN`
- **See which packages are installed** - `/lemmings/packages` lists your packages with name, link, purpose and version, behind the same token
- **Laravel Boost** - guideline and skill included, so an AI assistant in your app knows the package

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Installation

```bash
composer require darvis/lemmings
php artisan lemmings:install --url=https://your-own-site.example
```

The command writes `LEMMINGS_URL` and a fresh `LEMMINGS_CLEAR_TOKEN` to `.env` and shows the token once. Run it on every server, because `.env` is not in git.

## Quick start

Open `https://your-site.example/lemmings` and click the umbrella: your site opens in a new tab. To link to the page from a Blade view, use the route name, because the path can be changed with `LEMMINGS_ROUTE`:

```blade
<a href="{{ route('lemmings') }}" rel="nofollow">&pi;</a>
```

## Clearing the caches without a shell

Know this before you install: the package registers `GET /clearDgP`, which runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`. It answers 404 until you set a secret, and after that only a request that carries the secret gets through. Every refusal is the same 404 as a path that does not exist, and after five wrong tokens in a minute an IP address gets that 404 for every request until the minute is over.

`php artisan lemmings:install` makes the token for you. By hand:

```bash
php -r "echo bin2hex(random_bytes(24));"    # make a token
```

```env
LEMMINGS_CLEAR_TOKEN=paste-the-48-characters-here
```

```bash
curl -i -H "X-Lemmings-Token: paste-the-48-characters-here" https://your-site.example/clearDgP
```

From 1.5.0 to 1.6.0 this page was open to every visitor. Use 1.7.0 or later, and read the [security notes](https://arviddejong.github.io/lemmings/security.html).

## Which packages are installed

With the same token, `https://your-site.example/lemmings/packages?token=...` shows the packages from `config/lemmings.php` (by default a few `darvis/*` packages, `laravel-lang/common`, `laravel/ai`, `laravel/boost` and `sentry/sentry-laravel`): the name as a link, what the package is for, and the installed version or "not installed". Publish the config to change the list. Set `LEMMINGS_LAYOUT=layouts.app` to render the page in the layout component of your own application.

## Documentation

The full documentation lives on the [documentation site](https://arviddejong.github.io/lemmings/):

- [Installation](https://arviddejong.github.io/lemmings/installation.html): the steps, and how to check that it works
- [Quick start](https://arviddejong.github.io/lemmings/quick-start.html): your link, a login in front of the page, your own picture
- [Configuration](https://arviddejong.github.io/lemmings/configuration.html): the path, the link, the clear token, the layout and the package list
- [How it works](https://arviddejong.github.io/lemmings/how-it-works.html): the three routes, the token check and the views
- [Security and privacy](https://arviddejong.github.io/lemmings/security.html): what is public and what the token protects
- [Testing](https://arviddejong.github.io/lemmings/testing.html): test the page and the closed maintenance route in your app
- [Troubleshooting](https://arviddejong.github.io/lemmings/troubleshooting.html): a 404, a right token that is refused, the wrong link
- [FAQ](https://arviddejong.github.io/lemmings/faq.html)

## Laravel Boost

The package ships a guideline and a skill for [Laravel Boost](https://github.com/laravel/boost). Run `php artisan boost:install`, or `php artisan boost:update --discover` in a project that already uses Boost.

## Testing

```bash
composer test      # Pest
composer lint      # Pint, check only; composer format fixes
composer analyse   # Larastan
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## Support the package

If darvis/lemmings saves you time, buy me a beer 🍺: [sponsor me on GitHub](https://github.com/sponsors/ArvidDeJong).

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

## Security

Please report a vulnerability privately, as described in [SECURITY](SECURITY.md), not in the issue tracker.

## License

The MIT License (MIT). See [LICENSE](LICENSE.md).
