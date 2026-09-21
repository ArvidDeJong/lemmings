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
- **One answer for every refusal.** No configured token, a missing token, a wrong token and too many wrong tokens all give the same 404. It is the 404 your application gives for a path that does not exist: the same status, no rate limit headers, never a 429. So a GET request does not tell a visitor that the route is there, whether a token is set or how close a guess was. The token is compared with `hash_equals`, so the time a refusal takes says nothing about how close a guess was either.
- **Five wrong tokens a minute.** Wrong and missing tokens are counted for each IP address. After five within a minute, every request from that address gets the 404, also one with the right token, until that minute is over. A request with the right token is never counted.
- **What still shows the route.** The path is the same on every site and is in the public source code of the package. A refusal does a little more work than an unknown path, so the response time can differ. And other methods answer differently: `POST /clearDgP` gets a 405 with `Allow: GET, HEAD`, where a path that does not exist gets a 404. Treat the path as known. The token is what protects the route.
- **Header first.** The token is read from the `X-Lemmings-Token` header, and only when there is no such header from `?token=`.

```bash
curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP
```

```json
{"status": "success", "message": "All caches have been cleared and storage link recreated."}
```

### What the token protects

Whoever has the token can, as often as they like:

- empty the default cache store. Whatever your application keeps there is gone, which can include rate limiter counters such as the login throttle, and locks;
- remove the cached routes and config you built during a deploy. Laravel then reads the route and config files on every request until you cache them again.

The token gives no access to data and runs nothing but those seven commands.

### Keeping the token secret

- Generate a long random value: `php -r "echo bin2hex(random_bytes(24));"`. See [Configuration](configuration.md#the-clear-token).
- Prefer the header. `https://your-site.example/clearDgP?token=your-token` works in a browser, but the address ends up in the access log of the web server, in the browser history and in any proxy in between.
- When a token may have leaked, put a new value in `LEMMINGS_CLEAR_TOKEN`. Run `php artisan config:cache` again when you cache the config. The old token stops working.
- Remove the variable to close the route again.

### Older versions

In those versions `/clearDgP` had no token and no limit: every visitor could run the seven commands. The path is the same on every site and is in the public source code. Upgrade to 1.7.0 or later. In 1.7.0 the route had the token but still gave itself away: every answer carried `X-RateLimit` headers and the sixth request in a minute got a 429. Use the latest version. After the upgrade the route is closed until you set a token. If you closed it yourself with a route on the same path in `routes/web.php`, you can remove that line.

## Reporting a vulnerability

Report it privately through [GitHub private vulnerability reporting](https://github.com/ArvidDeJong/lemmings/security/advisories/new). The full policy is in [SECURITY.md](https://github.com/ArvidDeJong/lemmings/blob/main/SECURITY.md).
