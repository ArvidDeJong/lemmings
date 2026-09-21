# Lemmings

[![Latest version](https://img.shields.io/packagist/v/darvis/lemmings.svg)](https://packagist.org/packages/darvis/lemmings)
[![Tests](https://github.com/ArvidDeJong/lemmings/actions/workflows/tests.yml/badge.svg)](https://github.com/ArvidDeJong/lemmings/actions/workflows/tests.yml)
[![PHP version](https://img.shields.io/packagist/dependency-v/darvis/lemmings/php.svg)](https://packagist.org/packages/darvis/lemmings)
[![License](https://img.shields.io/packagist/l/darvis/lemmings.svg)](LICENSE.md)

A hidden Lemmings easter egg page for Laravel applications, with a link to your own site to prove who built it.

## Features

- **Nothing to set up** - install the package and `/lemmings` is there
- **Your path, your link** - `LEMMINGS_ROUTE` and `LEMMINGS_URL` in `.env`
- **Clear the caches without a shell** - a maintenance page that only exists with your secret token
- **Says nothing about the application** - no versions, no environment, nothing from the request on the page
- **Kept out of search engines** - the page carries `noindex, nofollow`
- **Your own page** - override the view in `resources/views/vendor/darvis-lemmings`
- **Laravel Boost** - guideline and skill included, so an AI assistant in your app knows the package

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Installation

```bash
composer require darvis/lemmings
```

```env
LEMMINGS_ROUTE=/lemmings
LEMMINGS_URL=https://your-own-site.example
```

## Quick start

Open `https://your-site.example/lemmings` and click the umbrella. In your own code the page is `route('lemmings')`.

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=lemmings-config
```

## Clearing the caches without a shell

The package also registers `GET /clearDgP`, which clears the caches and recreates the storage link. It answers 404 until you set a secret, and after that only for a request that carries it:

```env
LEMMINGS_CLEAR_TOKEN=a-long-random-value
```

```bash
php -r "echo bin2hex(random_bytes(24));"                                       # a token
curl -H "X-Lemmings-Token: a-long-random-value" https://your-site.example/clearDgP
```

From 1.5.0 to 1.6.0 this page was open to every visitor. Upgrade, and read the [security notes](https://arviddejong.github.io/lemmings/security.html).

## Documentation

The full documentation lives on the [documentation site](https://arviddejong.github.io/lemmings/):

- [Installation](https://arviddejong.github.io/lemmings/installation.html)
- [Configuration](https://arviddejong.github.io/lemmings/configuration.html): the path, the link and the clear token
- [How it works](https://arviddejong.github.io/lemmings/how-it-works.html): the routes, the view and your own page
- [Security and privacy](https://arviddejong.github.io/lemmings/security.html): what is public and what the token protects
- [FAQ](https://arviddejong.github.io/lemmings/faq.html)

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
