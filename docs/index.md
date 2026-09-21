---
title: "Home"
nav_order: 1
description: "darvis/lemmings adds a hidden Lemmings easter egg page to a Laravel application, with a link to the developer's site to prove who built it."
permalink: /
---

# Lemmings

`darvis/lemmings` adds a hidden page to a Laravel application: a Lemmings picture on a black background, in which the umbrella is a link to your own site. It also adds a maintenance page that clears the caches on hosting without shell access, which only works with a secret token you set.

## Who it is for

Developers and agencies who build Laravel sites for clients and want a quiet way to show "we built this": open `/lemmings` on the site and click the umbrella.

## What it does not do

- It does not add a visible credit, a footer link or anything else to your pages. Nobody finds the page unless they know the path.
- It shows nothing about the application: no Laravel or PHP version, no environment, no paths, and nothing from the request.
- It has no switch for middleware or a login. You add those with a route of your own, see [Quick start](quick-start.md).
- The maintenance page does nothing until you set `LEMMINGS_CLEAR_TOKEN`. Without it the page answers 404.

Versions 1.5.0 to 1.6.0 had the maintenance page open to every visitor. Use 1.7.0 or later, and read [Security and privacy](security.md).

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Install

```bash
composer require darvis/lemmings
```

```env
LEMMINGS_URL=https://your-own-site.example
```

Then open `https://your-site.example/lemmings`.

## Pages

- [Installation](installation.md): the steps, and how to check that it works
- [Quick start](quick-start.md): your link, a hidden link to the page, a login in front of it, your own picture
- [Configuration](configuration.md): the path, the link and the clear token
- [How it works](how-it-works.md): the two routes, the token check and the view
- [Security and privacy](security.md): what is public, what the page reveals and what the token protects
- [Testing](testing.md): test the page and the closed maintenance route in your application
- [Troubleshooting](troubleshooting.md): a 404, a right token that is refused, the wrong link and other symptoms
- [FAQ](faq.md): short answers

## Links

- [Source on GitHub](https://github.com/ArvidDeJong/lemmings)
- [Packagist](https://packagist.org/packages/darvis/lemmings)
- [Changelog](https://github.com/ArvidDeJong/lemmings/blob/main/CHANGELOG.md)
- [Report an issue](https://github.com/ArvidDeJong/lemmings/issues)
