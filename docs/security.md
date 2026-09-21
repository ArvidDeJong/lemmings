---
title: Security and privacy
nav_order: 5
description: "What darvis/lemmings makes public in a Laravel application: two routes without middleware, what the page reveals and how to close the maintenance route."
---

# Security and privacy

This package adds public routes to every application it is installed in. This page says exactly what they are.

## What is public

| Path | Who can open it | What happens |
| --- | --- | --- |
| `LEMMINGS_ROUTE`, default `/lemmings` | everybody | A static page is shown |
| `/clearDgP` | everybody | The caches are cleared and the storage link is recreated |

Neither route has middleware: no authentication, no rate limit, no session.

## What the easter egg page reveals

- That the package is installed, and the address in `LEMMINGS_URL`. With the default that is `https://lemmings.darvis.nl`, which tells a visitor who built the site. That is the purpose of the package.
- Nothing else. The page shows no Laravel or PHP version, no environment name, no debug state and no file paths.
- Nothing from the request is written into the page: no query string, no header, no cookie. The only dynamic value is `LEMMINGS_URL`, and it is escaped.
- The page sets no cookie and stores nothing about the visitor.

The test suite checks each of these points.

## The maintenance route

`GET /clearDgP` runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear` for anyone who requests it. Know what that means before you leave it open:

- `cache:clear` empties the default cache store. Whatever your application keeps there is gone, which can include rate limiter counters such as the login throttle, and locks.
- `route:clear` and `config:clear` remove the cached routes and config you built during a deploy. The site keeps working, but slower, until the next deploy.
- A visitor can repeat the request as often as they like.
- The path is the same in every application that has this package, and it is in the public source code. Do not treat it as a secret.

### Closing it

Pick one:

1. Replace it in your own `routes/web.php`. The last route on a path wins:

   ```php
   Route::get('/clearDgP', fn () => abort(404));
   ```

   or keep it, for people who are logged in:

   ```php
   Route::get('/clearDgP', function () {
       Artisan::call('optimize:clear');

       return response()->json(['status' => 'success']);
   })->middleware(['web', 'auth']);
   ```

2. Block the path in the web server or the firewall.
3. Don't load the package in that application: see [How it works](how-it-works.md#leaving-the-package-out-of-one-application).

Check the result with `curl -i https://your-site.example/clearDgP`.

## Reporting a vulnerability

Report it privately through [GitHub private vulnerability reporting](https://github.com/ArvidDeJong/lemmings/security/advisories/new). The full policy is in [SECURITY.md](https://github.com/ArvidDeJong/lemmings/blob/main/SECURITY.md).
