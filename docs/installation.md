---
title: Installation
nav_order: 2
description: "Install darvis/lemmings with Composer, open the easter egg page and publish the config file when you need it."
---

# Installation

```bash
composer require darvis/lemmings
```

Laravel discovers the service provider `Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider` by itself. There is no migration, no asset and no command to run.

## The first visit

Open `/lemmings` on your site. You see the picture, and a click on the umbrella opens the address from `LEMMINGS_URL` in a new tab. Out of the box that is `https://lemmings.darvis.nl`, so set your own:

```env
LEMMINGS_URL=https://your-own-site.example
```

In your own code the page is `route('lemmings')`.

## The config file

You only need the file when you don't want to use `.env`:

```bash
php artisan vendor:publish --tag=lemmings-config
```

This writes `config/lemmings.php`. See [Configuration](configuration.md).

## Before you deploy

The package registers two public routes. Read [Security and privacy](security.md) and decide whether you want both of them on a production site.

## Removing the package

```bash
composer remove darvis/lemmings
```

Delete `config/lemmings.php` and `resources/views/vendor/darvis-lemmings` when you created them. Run `php artisan route:cache` again when you cache your routes.
