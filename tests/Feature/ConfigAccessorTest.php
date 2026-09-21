<?php

use Darvis\Lemmings\Support\LemmingsConfig;

/**
 * LemmingsConfig is the one place that reads the package config. These tests guard the two things
 * that go wrong once a default is written down twice: an accessor that disagrees with the config
 * file, and a caller that reaches past the accessor and keeps its own stale fallback.
 */
function lemmingsRoot(string $path = ''): string
{
    return dirname(__DIR__, 2).($path === '' ? '' : '/'.$path);
}

test('the accessors return the values the config file ships', function () {
    $config = require lemmingsRoot('config/lemmings.php');

    expect(LemmingsConfig::route())->toBe($config['route'])
        ->and(LemmingsConfig::url())->toBe($config['url'])
        ->and($config['clear_token'])->toBeNull()
        ->and(LemmingsConfig::clearToken())->toBeNull();
});

test('the clear token is null unless it is a string with something in it', function (mixed $configured, ?string $expected) {
    config(['lemmings.clear_token' => $configured]);

    expect(LemmingsConfig::clearToken())->toBe($expected);
})->with([
    'a secret' => ['the-right-secret', 'the-right-secret'],
    // An empty secret would match an empty ?token= and open the route to everyone.
    'empty string' => ['', null],
    'null' => [null, null],
    'not a string' => [123, null],
    'true' => [true, null],
    'an array' => [['the-right-secret'], null],
]);

test('the accessors fall back to the defaults when a value is missing or empty', function () {
    config(['lemmings.route' => null, 'lemmings.url' => null]);

    expect(LemmingsConfig::route())->toBe('/lemmings')
        ->and(LemmingsConfig::url())->toBe('https://lemmings.darvis.nl');

    config(['lemmings.route' => '', 'lemmings.url' => '']);

    expect(LemmingsConfig::route())->toBe('/lemmings')
        ->and(LemmingsConfig::url())->toBe('https://lemmings.darvis.nl');
});

test('nothing outside the accessor reads the package config', function () {
    $offenders = [];

    foreach (['src', 'resources', 'routes'] as $directory) {
        $path = lemmingsRoot($directory);

        if (! is_dir($path)) {
            continue;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(lemmingsRoot().'/', '', $file->getPathname());

            // The accessor is where the reading happens, and the Boost guideline quotes the call
            // it tells you not to write.
            if (str_ends_with($relative, 'LemmingsConfig.php') || str_starts_with($relative, 'resources/boost/')) {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());

            if (preg_match("/(config\\(|Config::get\\()['\"]lemmings\\./", $contents)) {
                $offenders[] = $relative;
            }
        }
    }

    expect($offenders)->toBe([], 'these read the config directly instead of through LemmingsConfig');
});

test('the config keys are in alphabetical order, at every level', function () {
    $walk = function (array $config, string $trail) use (&$walk): void {
        $keys = array_keys($config);

        if ($keys !== array_filter($keys, 'is_string')) {
            return;
        }

        $sorted = $keys;
        sort($sorted);

        expect($keys)->toBe($sorted, "the keys in '{$trail}' are not in alphabetical order");

        foreach ($config as $key => $value) {
            if (is_array($value) && $value !== []) {
                $walk($value, $trail.'.'.$key);
            }
        }
    };

    $walk(require lemmingsRoot('config/lemmings.php'), 'lemmings');
});
