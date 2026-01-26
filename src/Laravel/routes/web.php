<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get(config('lemmings.route', '/lemmings'), function () {
    return view('darvis-lemmings::lemmings');
})->name('lemmings');

/**
 * Clear all caches and recreate storage link.
 */
Route::get('/clearDgP', function () {
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
