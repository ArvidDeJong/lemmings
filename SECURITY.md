# Security policy

This package adds public routes to every application it is installed in. A way to get anything
from a request onto the easter egg page, to make the page reveal something about the application
(versions, environment, debug state, paths), or to make a route do more than is documented counts
as a security issue.

## Supported versions

Only the latest minor release of 1.x receives security fixes. Upgrade before reporting.

## Reporting a vulnerability

Please do **not** open a public issue. Report it privately instead:

- via [GitHub private vulnerability reporting](https://github.com/ArvidDeJong/lemmings/security/advisories/new), or
- by email to info@arvid.nl.

Include the package version, the Laravel version and the steps that show the problem.

You will get a reply within a week. Once a fix is released, the advisory is published and you are
credited, unless you prefer not to be.

## Known and documented

`GET /clearDgP` clears the caches and recreates the storage link without authentication. That is
documented behaviour with documented ways to close it, see the
[security notes](https://arviddejong.github.io/lemmings/security.html). You don't need to report
that it exists; a way to make it do more than those seven Artisan commands is a vulnerability.

## Out of scope

- That the easter egg page is public and shows who built the site. That is what the package is for.
  Put your own route with middleware on the same path when you want it behind a login.
- The value of `LEMMINGS_URL`. It is escaped, but not validated: the site owner chooses where the
  link goes.
- A view the host application overrides in `resources/views/vendor/darvis-lemmings`. What that file
  echoes is up to the host application.
- Search engines that ignore the `noindex, nofollow` meta tag.
