# Laravel Lemmings Easter Egg

A fun easter egg for your Laravel website that proves you are the developer.

## Installation

You can install this package via Composer:

```bash
composer require darvis/lemmings
```

### Requirements
- PHP ^8.2
- Laravel ^11.0 or ^12.0

## Configuration

The package works automatically with Laravel's auto-discovery. After installation, no further configuration is required.

Optionally, you can publish the configuration file:

```bash
php artisan vendor:publish --tag=lemmings-config
```

This will create a `config/lemmings.php` file where you can customize:

```php
return [
    // The URL the easter egg links to
    'url' => env('LEMMINGS_URL', 'https://lemmings.darvis.nl'),

    // The route path for the easter egg
    'route' => env('LEMMINGS_ROUTE', '/lemmings'),
];
```

## Usage

Simply add `/lemmings` to your website URL:

```
https://your-website.com/lemmings
```

Or use the named route in your application:

```php
route('lemmings')
```

## Features
- Automatic route registration
- Zero-configuration setup
- SEO-friendly (hidden from search engines)
- Works with Livewire 3
- Compatible with FluxUI
- Support for Laravel 12

## Changelog

See [CHANGELOG](CHANGELOG.md) for all changes.

## Credits

- [Arvid de Jong](https://darvis.nl)
- [All contributors](../../contributors)

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
