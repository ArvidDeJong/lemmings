---
title: "Troubleshooting"
nav_order: 8
description: "Fix darvis/lemmings by symptom: a 404 on /lemmings or /clearDgP, a 429, the umbrella opening the wrong site, Route [lemmings] not defined, a failing route cache."
---

# Troubleshooting

Each entry is a symptom, the cause and the fix. Several of them are a cache that is older than your change: the package reads its settings from the config, and the path of the page while the application boots. These two commands remove those caches:

```bash
php artisan config:clear
php artisan route:clear
```

Run `php artisan config:cache` and `php artisan route:cache` again afterwards when your deploy uses them.

## `/lemmings` gives a 404

| Cause | Fix |
| --- | --- |
| `LEMMINGS_ROUTE` is set to another path | Open that path, or look it up: `php artisan route:list --name=lemmings` |
| The routes were cached before the package was installed or before the path changed | `php artisan route:clear`, or `php artisan route:cache` again |
| The package is not loaded: it is listed under `extra.laravel.dont-discover` in your `composer.json` | Remove it from that list and run `composer dump-autoload` |

## `Route [lemmings] not defined.`

A view calls `route('lemmings')`, but the package route is not there. The causes are the last two of the table above. When you removed the package, remove the link from your views too.

## The umbrella opens `lemmings.darvis.nl` instead of your site

| Cause | Fix |
| --- | --- |
| `LEMMINGS_URL` is not in `.env`, or it is empty | Set it. An empty value counts as not set |
| The config cache is older than your `.env` | `php artisan config:clear`, or `php artisan config:cache` again |
| You published `config/lemmings.php` and changed `url` there to a fixed value | Put `env('LEMMINGS_URL', ...)` back, or change the value in that file |

## `/clearDgP` gives a 404 although you send the token

Every refusal is the same 404, so check these one by one:

| Cause | Fix |
| --- | --- |
| `LEMMINGS_CLEAR_TOKEN` is not set on this server, or it is empty | Set it in the `.env` of the server you are calling |
| The config cache is older than your `.env` | `php artisan config:clear`, or `php artisan config:cache` again |
| The request has a wrong `X-Lemmings-Token` header and a right `?token=` | The header wins. Send one of the two |
| The token in the address contains characters such as `+`, `&` or `#` | Use a token from `php -r "echo bin2hex(random_bytes(24));"`, which needs no encoding, or send it in the header |
| Your published `config/lemmings.php` has a `clear_token` key with a fixed value or `null` | A key in your file wins over the package. Make it `'clear_token' => env('LEMMINGS_CLEAR_TOKEN'),` or remove the line |
| Your application has its own route on `/clearDgP` | The last route on a path wins. Remove yours when you want the package route back |

A published config file without a `clear_token` key is not a cause: Laravel fills the missing key from the package.

## `/clearDgP` gives a 429 Too Many Requests

A request that asks for JSON gets the message `Too Many Attempts.`. The route allows five requests a minute for each visitor, counted by IP address, with or without the right token. Wait a minute. The `Retry-After` header of the answer says how many seconds.

When you get a 429 on your first request, somebody else shares your counter. Behind a load balancer or proxy every visitor has the same IP address until your application trusts the proxy; see "Configuring Trusted Proxies" in the Laravel documentation.

## The answer says `success`, but the storage link is missing

The route calls the seven commands and does not look at what they report. `{"status":"success", ...}` means they were called and none of them threw an exception. A command that reports a problem without throwing still ends in `success`, and its output is not shown. `storage:link`, for example, reports `The [/path/to/public/storage] link already exists.` when something is already at that place. Run the single command where you can, to see its output.

## The site reads config and routes from the files again after a call

That is what the route does: `config:clear`, `route:clear` and `optimize:clear` remove the caches a deploy built. Run `php artisan config:cache` and `php artisan route:cache` again, or deploy again.

## Route cache fails on the name `lemmings`

For example:

```text
Unable to prepare route [built-by] for serialization. Another route has already been assigned name [lemmings].
```

Your application has a route named `lemmings` on another path than the package route. Two routes with one name cannot be cached. Give your route the same path as `LEMMINGS_ROUTE`, so it replaces the package route, or another name.

## Your own view is not used

The copy must be exactly `resources/views/vendor/darvis-lemmings/lemmings.blade.php`. The folder is the view namespace `darvis-lemmings`, not the package name. Run `php artisan view:clear` afterwards.

## `auth()->user()` is `null`, or `@csrf` fails, in your own view

The package route is not in the `web` group, so there is no session. Replace the route with your own and give it the `web` middleware: see [Quick start](quick-start.md#put-a-login-in-front-of-the-page).

## Still stuck

[Open an issue](https://github.com/ArvidDeJong/lemmings/issues/new/choose) with the path you opened, the status code and your `LEMMINGS_ROUTE`. Leave your token out. For a security problem, see [Security and privacy](security.md#reporting-a-vulnerability).
