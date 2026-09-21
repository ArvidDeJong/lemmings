---
title: Home
nav_order: 1
description: "A hidden Lemmings easter egg page for Laravel applications, with a link to the developer's site to prove who built it."
permalink: /
---

# Lemmings

`darvis/lemmings` adds a hidden page to a Laravel application: a Lemmings picture on a black background. The umbrella in the picture is a link to your own site. Open `/lemmings` on a site you built and you can show that it is yours.

- **Nothing to set up**: install the package and the page is there.
- **Your path, your link**: both come from `.env`.
- **Says nothing about the application**: no versions, no environment, nothing from the request.
- **Clear the caches without a shell**: a maintenance route that only exists for a request with your secret token.

Versions 1.5.0 to 1.6.0 had that maintenance route open to every visitor. Use 1.7.0 or later, and read the [security notes](security.md).

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Install

```bash
composer require darvis/lemmings
```

```env
LEMMINGS_ROUTE=/lemmings
LEMMINGS_URL=https://your-own-site.example
```

Then open `https://your-site.example/lemmings`.

## Pages

- [Installation](installation.md): the package and the first visit
- [Configuration](configuration.md): the path, the link and the clear token
- [How it works](how-it-works.md): the routes, the token check, the view and how to use your own page
- [Security and privacy](security.md): what is public, what the page reveals and what the token protects
- [FAQ](faq.md)

## Links

- [Source on GitHub](https://github.com/ArvidDeJong/lemmings)
- [Packagist](https://packagist.org/packages/darvis/lemmings)
- [Changelog](https://github.com/ArvidDeJong/lemmings/blob/main/CHANGELOG.md)
- [Report an issue](https://github.com/ArvidDeJong/lemmings/issues)
