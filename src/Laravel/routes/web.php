<?php

declare(strict_types=1);

use Darvis\Lemmings\Support\LemmingsConfig;
use Darvis\Lemmings\Support\TokenGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get(LemmingsConfig::route(), function () {
    return view('darvis-lemmings::lemmings');
})->name('lemmings');

/**
 * Which of the configured packages Composer installed in this application, with their versions.
 *
 * That is information about the application, so the page is behind the same token as the
 * maintenance route and answers 404 without it. See TokenGuard for the rules.
 */
Route::get(LemmingsConfig::packagesRoute(), function (Request $request) {
    TokenGuard::check($request);

    return view('darvis-lemmings::packages');
})->name('lemmings.packages');

/**
 * Clear all caches and recreate the storage link, for a site owner on hosting without a shell.
 *
 * Only with the secret from LEMMINGS_CLEAR_TOKEN; see TokenGuard for the rules and why every
 * refusal is a 404. From 1.5.0 to 1.6.0 this route was open to every visitor, who could reset the
 * rate limiters that live in the cache and undo the route and config cache of a deploy.
 */
Route::get('/clearDgP', function (Request $request) {
    TokenGuard::check($request);

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
