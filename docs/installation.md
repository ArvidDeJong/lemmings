---
title: "Installation"
nav_order: 2
description: "Install darvis/lemmings step by step, set your link and the optional clear token, and check with a browser and curl that both routes answer as they should."
---

# Installation

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

## Steps

1. Install the package:

   ```bash
   composer require darvis/lemmings
   ```

   Laravel finds the service provider by itself (package discovery). There is no migration and no asset.

2. Make the token and set your link, in one go:

   ```bash
   php artisan lemmings:install --url=https://your-own-site.example
   ```

   The command writes `LEMMINGS_URL` and a fresh `LEMMINGS_CLEAR_TOKEN` (48 hex characters) to `.env` and shows the token once; copy it somewhere safe. A token that is already in `.env` is kept, unless you pass `--force`. Without `.env` the command stops and writes nothing.

   You can also do it by hand. Without `LEMMINGS_URL` the umbrella links to `https://lemmings.darvis.nl`; without `LEMMINGS_CLEAR_TOKEN` the maintenance page and the packages page stay closed:

   ```bash
   php -r "echo bin2hex(random_bytes(24));"
   ```

   ```env
   LEMMINGS_URL=https://your-own-site.example
   LEMMINGS_CLEAR_TOKEN=paste-the-48-characters-here
   ```

3. When your application caches its config or routes, build the caches again:

   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

`.env` is not in git, so run `lemmings:install` on every server, or copy the token there.

All settings are on the [Configuration](configuration.md) page.

## Check that it works

**The easter egg page.** Open `https://your-site.example/lemmings` in a browser. You see a black page with the picture "Oh no! More Lemmings!". Click the umbrella: the address from `LEMMINGS_URL` opens in a new tab.

**The maintenance page without a token.** It must answer 404:

```bash
curl -i https://your-site.example/clearDgP
```

```text
HTTP/1.1 404 Not Found
```

The first line can say `HTTP/2 404` instead; the number is what counts.

**The maintenance page with the token**, when you did step 3. It answers 200 and a line of JSON:

```bash
curl -i -H "X-Lemmings-Token: paste-the-48-characters-here" https://your-site.example/clearDgP
```

```text
HTTP/1.1 200 OK

{"status":"success","message":"All caches have been cleared and storage link recreated."}
```

After this call the config and route caches are gone. Run step 4 again when you use them.

| You see | It means |
| --- | --- |
| 404 on `/lemmings` | The path was changed with `LEMMINGS_ROUTE`, or the routes were cached before you installed the package |
| The umbrella opens `lemmings.darvis.nl` | `LEMMINGS_URL` is not set, or the config cache is older than your `.env` |
| 404 on `/clearDgP` with the token | The token does not match, or the config cache is older than your `.env` |
| 404 on `/clearDgP` with the right token, after a few failed tries | Five wrong tokens came from your IP address within a minute. Wait a minute |

Each of these is worked out on the [Troubleshooting](troubleshooting.md) page.

## Publish the config file

You only need the file when you don't want to use `.env`:

```bash
php artisan vendor:publish --tag=lemmings-config
```

This writes `config/lemmings.php`.

## Upgrade from 1.5.0 or 1.6.0

In those versions `/clearDgP` was open to every visitor. After the upgrade it is closed. If you use it, do step 3 and send the token with the request. If you closed it with your own route on `/clearDgP`, you can remove that route.

## Remove the package

```bash
composer remove darvis/lemmings
```

Delete `config/lemmings.php` and `resources/views/vendor/darvis-lemmings` when you created them, and remove `route('lemmings')` from your views. Run `php artisan route:cache` again when you cache your routes.
