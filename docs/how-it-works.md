---
title: "How it works"
nav_order: 5
description: "What darvis/lemmings registers in a Laravel application: two GET routes, the token check and the wrong token limit on /clearDgP, the static view, and how to leave it out."
---

# How it works

The service provider does three things: it merges `config/lemmings.php`, it loads one routes file and it registers the view namespace `darvis-lemmings`.

## The routes

| Name | Method | Path | Middleware | Answer |
| --- | --- | --- | --- | --- |
| `lemmings` | GET | `LEMMINGS_ROUTE`, default `/lemmings` | none | The view `darvis-lemmings::lemmings` |
| `lemmings.clear` | GET | `/clearDgP` | none | With the right token: JSON, after clearing the caches. Otherwise 404 |

Neither route has middleware, so neither is in the `web` group. The easter egg page therefore starts no session, sets no cookie and does not know who is logged in.

## The maintenance route

`lemmings.clear` is meant for hosting without shell access. A request goes through these steps:

1. Without a configured `LEMMINGS_CLEAR_TOKEN`, or with an empty one, the answer is a 404.
2. The route looks up how many wrong tokens came from the IP address of the request in the last minute. After five, the answer is a 404 and the token is not looked at, also when it is the right one.
3. The token is taken from the `X-Lemmings-Token` header. Only when the request has no such header, it is taken from `?token=`.
4. A missing or wrong token is counted for that IP address and gets a 404. A request with the right token is not counted.
5. With the right token the route runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`, and answers with:

```json
{"status": "success", "message": "All caches have been cleared and storage link recreated."}
```

```bash
curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP
```

The route does not look at what the seven commands report. `"status": "success"` means they were called and none threw an exception, not that each one did its work.

Every refusal is the 404 your application gives for a path that does not exist: same status, no rate limit headers, no 429.

See [Configuration](configuration.md#the-clear-token) for the token and [Security and privacy](security.md) for what it protects.

## The page

The view is one static HTML file:

- the title `Oh no more Lemmings....` and a black background;
- one picture of 700 by 600 pixels, embedded in the page itself, so there is no second request and no asset to publish;
- an image map with one clickable area, the umbrella, that opens `LEMMINGS_URL` in a new tab;
- a `robots` meta tag with `noindex, nofollow`.

It has no script, no stylesheet and no form.

## Changing the page or the route

The package has no setting for middleware or for another view. Both are done from your application: a view in `resources/views/vendor/darvis-lemmings` replaces the page, and a route on the same path in `routes/web.php` replaces the route, because the routes of your application are registered after those of the package and the last route on a path wins. [Quick start](quick-start.md) has both examples.

## Leaving the package out of one application

To keep the package installed but load nothing of it, tell Laravel not to discover it, in the `composer.json` of your application:

```json
"extra": {
    "laravel": {
        "dont-discover": ["darvis/lemmings"]
    }
}
```

Run `composer dump-autoload` afterwards. Both routes are gone.
