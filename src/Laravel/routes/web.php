<?php

declare(strict_types=1);

use Darvis\Lemmings\Support\LemmingsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

Route::get(LemmingsConfig::route(), function () {
    return view('darvis-lemmings::lemmings');
})->name('lemmings');

/**
 * Clear all caches and recreate the storage link, for a site owner on hosting without a shell.
 *
 * Only with the secret from LEMMINGS_CLEAR_TOKEN, as `?token=` or in the X-Lemmings-Token header.
 * From 1.5.0 to 1.6.0 this route was open to every visitor, who could reset the rate limiters that
 * live in the cache and undo the route and config cache of a deploy.
 *
 * Every refusal is the 404 of a path that does not exist: no configured secret, a missing or wrong
 * one, and too many wrong ones. That is why the limit is counted here and not by the `throttle`
 * middleware: that one answers 429 and puts X-RateLimit headers on every response, which tells a
 * stranger the route is there. Only wrong tokens count, five a minute per IP address, and while an
 * address is over the limit the token is not looked at, so guessing stays slow.
 */
Route::get('/clearDgP', function (Request $request) {
    $expected = LemmingsConfig::clearToken();
    $given = $request->header('X-Lemmings-Token') ?? $request->query('token');

    abort_if($expected === null, 404);

    $attempts = 'lemmings-clear:'.$request->ip();

    abort_if(RateLimiter::tooManyAttempts($attempts, 5), 404);

    if (! is_string($given) || ! hash_equals($expected, $given)) {
        RateLimiter::hit($attempts, 60);

        abort(404);
    }

    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('storage:link');
    Artisan::call('event:clear');
    Artisan::call('optimize:clear');

    return response()->json([
        'status' => 'success',
        'message' => 'All caches have been cleared and storage link recreated.',
    ]);
})->name('lemmings.clear');
