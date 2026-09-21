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
- **Laravel Boost** - guideline and skill included, so an AI assistant in your app knows the package

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Installation

```bash
composer require darvis/lemmings
```

`.env`:

```env
LEMMINGS_URL=https://your-own-site.example
```

## Quick start

Open `https://your-site.example/lemmings` and click the umbrella: your site opens in a new tab. To link to the page from a Blade view, use the route name, because the path can be changed with `LEMMINGS_ROUTE`:

```blade
<a href="{{ route('lemmings') }}" rel="nofollow">&pi;</a>
```

## Clearing the caches without a shell

Know this before you install: the package registers `GET /clearDgP`, which runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`. It answers 404 until you set a secret, and after that only a request that carries the secret gets through. It allows five requests a minute.

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

## Documentation

The full documentation lives on the [documentation site](https://arviddejong.github.io/lemmings/):

- [Installation](https://arviddejong.github.io/lemmings/installation.html): the steps, and how to check that it works
- [Quick start](https://arviddejong.github.io/lemmings/quick-start.html): your link, a login in front of the page, your own picture
- [Configuration](https://arviddejong.github.io/lemmings/configuration.html): the path, the link and the clear token
- [How it works](https://arviddejong.github.io/lemmings/how-it-works.html): the two routes, the token check and the view
- [Security and privacy](https://arviddejong.github.io/lemmings/security.html): what is public and what the token protects
- [Testing](https://arviddejong.github.io/lemmings/testing.html): test the page and the closed maintenance route in your app
- [Troubleshooting](https://arviddejong.github.io/lemmings/troubleshooting.html): a 404, a 429, the wrong link
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

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

## Security

Please report a vulnerability privately, as described in [SECURITY](SECURITY.md), not in the issue tracker.

## License

The MIT License (MIT). See [LICENSE](LICENSE.md).
