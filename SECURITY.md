# Security policy

This package adds routes to every application it is installed in: a public easter egg page, and a
maintenance page that clears the caches for whoever has the secret from `LEMMINGS_CLEAR_TOKEN`.

These count as a security issue:

- a way to get anything from a request onto the easter egg page, or to make it reveal something
  about the application (versions, environment, debug state, paths);
- a way to run the maintenance route without the configured token, with an empty token, or while no
  token is configured;
- a way to tell from the outside whether a token is configured, or to read the token from a
  response, a log line the package writes or an error message;
- a way around the throttle on the maintenance route, or a way to make the route do more than its
  seven Artisan commands.

## Supported versions

Only the latest minor release of 1.x receives security fixes. Upgrade before reporting.

Versions 1.5.0 to 1.6.0 have the maintenance route open to every visitor. That is fixed in 1.7.0;
the CHANGELOG says what to do.

## Reporting a vulnerability

Please do **not** open a public issue. Report it privately instead:

- via [GitHub private vulnerability reporting](https://github.com/ArvidDeJong/lemmings/security/advisories/new), or
- by email to info@arvid.nl.

Include the package version, the Laravel version and the steps that show the problem. Leave your
token out.

You will get a reply within a week. Once a fix is released, the advisory is published and you are
credited, unless you prefer not to be.

## What the token protects, and what to do when it leaks

Whoever has the token can clear the default cache store, which can hold rate limiter counters such
as the login throttle, and can remove the cached routes and config of a deploy, as often as the
throttle of five requests a minute allows. The token gives no access to data and runs nothing but
those seven commands.

Treat it like a password. Send it in the `X-Lemmings-Token` header; a `?token=` in the address
ends up in the access log of the web server, in the browser history and in any proxy in between.
When a token may have leaked, put a new value in `LEMMINGS_CLEAR_TOKEN` (and run
`php artisan config:cache` again when you cache the config). The old one stops working at once.
Remove the variable to close the page for everyone.

## Out of scope

- That the easter egg page is public and shows who built the site. That is what the package is for.
  Put your own route with middleware on the same path when you want it behind a login.
- A weak or shared token, a token sent in the query string, or a token committed to a repository.
  The package compares what you configure; choosing and keeping the secret is up to the site owner.
- The value of `LEMMINGS_URL`. It is escaped, but not validated: the site owner chooses where the
  link goes.
- A view or a route the host application overrides. What that code does is up to the host
  application.
- Search engines that ignore the `noindex, nofollow` meta tag.
