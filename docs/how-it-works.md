---
title: "How it works"
nav_order: 5
description: "What darvis/lemmings registers in a Laravel application: three GET routes, the token check and the wrong token limit on /clearDgP and the packages page, the views, and how to leave it out."
---

# How it works

The service provider does three things: it merges `config/lemmings.php`, it loads one routes file and it registers the view namespace `darvis-lemmings`.

## The routes

| Name | Method | Path | Middleware | Answer |
| --- | --- | --- | --- | --- |
| `lemmings` | GET | `LEMMINGS_ROUTE`, default `/lemmings` | none | The view `darvis-lemmings::lemmings` |
| `lemmings.packages` | GET | `LEMMINGS_ROUTE` plus `/packages`, default `/lemmings/packages` | none | With the right token: the view `darvis-lemmings::packages`. Otherwise 404 |
| `lemmings.clear` | GET | `/clearDgP` | none | With the right token: JSON, after clearing the caches. Otherwise 404 |

No route has middleware, so none is in the `web` group. The pages therefore start no session, set no cookie and do not know who is logged in.

## The token check

`lemmings.packages` and `lemmings.clear` share one check, `Darvis\Lemmings\Support\TokenGuard`. A request goes through these steps:

1. Without a configured `LEMMINGS_CLEAR_TOKEN`, or with an empty one, the answer is a 404.
2. The route looks up how many wrong tokens came from the IP address of the request in the last minute. After five, the answer is a 404 and the token is not looked at, also when it is the right one.
3. The token is taken from the `X-Lemmings-Token` header. Only when the request has no such header, it is taken from `?token=`.
4. A missing or wrong token is counted for that IP address and gets a 404. A request with the right token is not counted.
5. With the right token the request reaches the route.

The limit is one counter for both routes: five wrong tokens on the packages page also close `/clearDgP` for that minute.

Every refusal is the 404 your application gives for a path that does not exist: same status, no rate limit headers, no 429.

See [Configuration](configuration.md#the-clear-token) for the token and [Security and privacy](security.md) for what it protects.

## The maintenance route

`lemmings.clear` is meant for hosting without shell access. With the right token it runs `cache:clear`, `route:clear`, `config:clear`, `view:clear`, `storage:link`, `event:clear` and `optimize:clear`, and answers with:

```json
{"status": "success", "message": "All caches have been cleared and storage link recreated."}
```

```bash
curl -H "X-Lemmings-Token: your-token" https://your-site.example/clearDgP
```

The route does not look at what the seven commands report. `"status": "success"` means they were called and none threw an exception, not that each one did its work.

## The packages page

`lemmings.packages` reads the `lemmings.packages` config and asks Composer's `InstalledVersions` for each entry whether it is installed in the application, and which version. The view `darvis-lemmings::packages` shows one row per entry: the name as a link to `url`, the Composer name, the version or "not installed", and the purpose. It is plain HTML with its own small stylesheet, rendered inside the layout component from `LEMMINGS_LAYOUT`, or inside the package's own `darvis-lemmings::layout` when none is set. See [Configuration](configuration.md#the-packages).

## The easter egg page

The view is one static HTML file:

- the title `Oh no more Lemmings....` and a black background;
- one picture of 700 by 600 pixels, embedded in the page itself, so there is no second request and no asset to publish;
- an image map with one clickable area, the umbrella, that opens `LEMMINGS_URL` in a new tab;
- a `robots` meta tag with `noindex, nofollow`.

It has no script, no stylesheet and no form.

## Changing a page or a route

The package has no setting for middleware or for another view. Both are done from your application: a view in `resources/views/vendor/darvis-lemmings` replaces a page (`lemmings.blade.php`, `packages.blade.php` or `components/layout.blade.php`), and a route on the same path in `routes/web.php` replaces a route, because the routes of your application are registered after those of the package and the last route on a path wins. [Quick start](quick-start.md) has both examples.

## Leaving the package out of one application

To keep the package installed but load nothing of it, tell Laravel not to discover it, in the `composer.json` of your application:

```json
"extra": {
    "laravel": {
        "dont-discover": ["darvis/lemmings"]
    }
}
```

Run `composer dump-autoload` afterwards. All three routes are gone.
