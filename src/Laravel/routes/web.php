<?php

declare(strict_types=1);

use Darvis\Lemmings\Support\LemmingsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get(LemmingsConfig::route(), function () {
    return view('darvis-lemmings::lemmings');
})->name('lemmings');

/**
 * Clear all caches and recreate the storage link, for a site owner on hosting without a shell.
 *
 * Only with the secret from LEMMINGS_CLEAR_TOKEN, as `?token=` or in the X-Lemmings-Token header.
 * Without a configured secret, and for a missing or wrong one, the answer is a plain 404: from
 * 1.5.0 to 1.6.0 this route was open to every visitor, who could reset the rate limiters that live
 * in the cache and undo the route and config cache of a deploy. The throttle keeps guessing slow.
 */
Route::get('/clearDgP', function (Request $request) {
    $expected = LemmingsConfig::clearToken();
    $given = $request->header('X-Lemmings-Token') ?? $request->query('token');

    abort_unless($expected !== null && is_string($given) && hash_equals($expected, $given), 404);

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
})->middleware('throttle:5,1')->name('lemmings.clear');
