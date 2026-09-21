<?php

use Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider;
use Illuminate\Support\ServiceProvider;

it('merges the package config', function () {
    expect(config('lemmings.route'))->toBe('/lemmings')
        ->and(config('lemmings.url'))->toBe('https://lemmings.darvis.nl');
});

it('offers the config file for publishing', function () {
    // Never run vendor:publish here: it writes into the Testbench app.
    $paths = ServiceProvider::pathsToPublish(DarvisLemmingsProvider::class, 'lemmings-config');

    expect($paths)->toHaveCount(1)
        ->and(realpath((string) array_key_first($paths)))->toBe(realpath(dirname(__DIR__, 2).'/config/lemmings.php'))
        ->and(array_values($paths)[0])->toBe(config_path('lemmings.php'));
});

it('publishes nothing but the config file', function () {
    // There is no publish tag for the view. A host app overrides it in
    // resources/views/vendor/darvis-lemmings instead.
    expect(ServiceProvider::pathsToPublish(DarvisLemmingsProvider::class))->toHaveCount(1);
});

it('registers the view namespace', function () {
    expect(view()->exists('darvis-lemmings::lemmings'))->toBeTrue();
});

it('is the provider composer.json announces for auto-discovery', function () {
    $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);

    expect($composer['extra']['laravel']['providers'])->toBe([DarvisLemmingsProvider::class])
        ->and($composer)->not->toHaveKey('version');
});
