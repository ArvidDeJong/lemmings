<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * The token check shared by the maintenance route and the packages page.
 *
 * Only a request with the secret from LEMMINGS_CLEAR_TOKEN, as `?token=` or in the X-Lemmings-Token
 * header, gets through. Every refusal is the 404 of a path that does not exist: no configured
 * secret, a missing or wrong one, and too many wrong ones. That is why the limit is counted here
 * and not by the `throttle` middleware: that one answers 429 and puts X-RateLimit headers on every
 * response, which tells a stranger the route is there. Only wrong tokens count, five a minute per IP
 * address over both routes together, and while an address is over the limit the token is not looked
 * at, so guessing stays slow.
 */
final class TokenGuard
{
    /**
     * Abort with a 404 unless the request carries the configured token.
     */
    public static function check(Request $request): void
    {
        $expected = LemmingsConfig::clearToken();
        $given = $request->header('X-Lemmings-Token') ?? $request->query('token');

        abort_if($expected === null, 404);

        $attempts = 'lemmings-clear:'.$request->ip();

        abort_if(RateLimiter::tooManyAttempts($attempts, 5), 404);

        if (! is_string($given) || ! hash_equals($expected, $given)) {
            RateLimiter::hit($attempts, 60);

            abort(404);
        }
    }
}
