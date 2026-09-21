---
title: "Security and privacy"
nav_order: 6
description: "Is darvis/lemmings safe? A public easter egg page that reveals nothing about the app, and a cache clearing route that answers 404 without your secret token."
---

# Security and privacy

This package adds two routes to every application it is installed in. This page says exactly what they are.

## What is reachable

| Path | Who can use it | What happens |
| --- | --- | --- |
| `LEMMINGS_ROUTE`, default `/lemmings` | everybody | A static page is shown |
| `/clearDgP` | whoever has the secret from `LEMMINGS_CLEAR_TOKEN` | The caches are cleared and the storage link is recreated |
| `/clearDgP` | everybody else, and everybody while no token is set | 404 |

## What the easter egg page reveals

- That the package is installed, and the address in `LEMMINGS_URL`. With the default that is `https://lemmings.darvis.nl`, which tells a visitor who built the site. That is the purpose of the package.
- Nothing else. The page shows no Laravel or PHP version, no environment name, no debug state and no file paths.
- Nothing from the request is written into the page: no query string, no header, no cookie. The only dynamic value is `LEMMINGS_URL`, and it is escaped.
- The route has no middleware. The page sets no cookie and stores nothing about the visitor.

The test suite checks each of these points.

## The maintenance route

`GET /clearDgP` runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`. It is there for hosting without shell access.

- **Closed by default.** Without `LEMMINGS_CLEAR_TOKEN`, or with an empty one, the route answers 404 for every request. An empty `?token=` never matches.
- **One answer for every refusal.** A missing token, a wrong token and no configured token all give the same 404, so the answer does not tell a visitor whether a token is set or how close a guess was. It does show that the path exists: the 404 carries the `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers of the throttle, which a 404 for an unknown path does not have. The token is compared with `hash_equals`, so the time a refusal takes says nothing about how close a guess was.
- **Throttled.** The route allows five requests a minute for each visitor, counted by IP address, right or wrong. After that it answers 429 until the minute is over.
- **Header first.** The token is read from the `X-Lemmings-Token` header, and only when there is no such header from `?token=`.

```bash
curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP
```

```json
{"status": "success", "message": "All caches have been cleared and storage link recreated."}
```

### What the token protects

Whoever has the token can, as often as the throttle allows:

- empty the default cache store. Whatever your application keeps there is gone, which can include rate limiter counters such as the login throttle, and locks;
- remove the cached routes and config you built during a deploy. Laravel then reads the route and config files on every request until you cache them again.

The token gives no access to data and runs nothing but those seven commands.

### Keeping the token secret

- Generate a long random value: `php -r "echo bin2hex(random_bytes(24));"`. See [Configuration](configuration.md#the-clear-token).
- Prefer the header. `https://your-site.example/clearDgP?token=your-token` works in a browser, but the address ends up in the access log of the web server, in the browser history and in any proxy in between.
- When a token may have leaked, put a new value in `LEMMINGS_CLEAR_TOKEN`. Run `php artisan config:cache` again when you cache the config. The old token stops working.
- Remove the variable to close the route again.

### Versions 1.5.0 to 1.6.0

In those versions `/clearDgP` had no token and no throttle: every visitor could run the seven commands. The path is the same on every site and is in the public source code. Upgrade to 1.7.0 or later. After the upgrade the route is closed until you set a token. If you closed it yourself with a route on the same path in `routes/web.php`, you can remove that line.

## Reporting a vulnerability

Report it privately through [GitHub private vulnerability reporting](https://github.com/ArvidDeJong/lemmings/security/advisories/new). The full policy is in [SECURITY.md](https://github.com/ArvidDeJong/lemmings/blob/main/SECURITY.md).
