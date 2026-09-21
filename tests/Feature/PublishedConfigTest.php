<?php

use Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider;
use Darvis\Lemmings\Support\LemmingsConfig;

/**
 * A config file published before 1.7.0 has `route` and `url`, but no `clear_token` key.
 * mergeConfigFrom() merges the top level keys, so the missing key comes from the package config and
 * LEMMINGS_CLEAR_TOKEN works without touching the published file. The CHANGELOG and the docs
 * promise that.
 */
afterEach(function () {
    unset($_ENV['LEMMINGS_CLEAR_TOKEN'], $_SERVER['LEMMINGS_CLEAR_TOKEN']);
});

/**
 * Register the provider again on top of the config a host app loaded from its own file.
 *
 * @param  array<string, mixed>  $published
 */
function registerOnTopOf(array $published): void
{
    config(['lemmings' => $published]);

    (new DarvisLemmingsProvider(app()))->register();
}

it('fills the clear_token key a published config does not have, from the environment', function () {
    $_ENV['LEMMINGS_CLEAR_TOKEN'] = 'secret-from-the-environment';
    $_SERVER['LEMMINGS_CLEAR_TOKEN'] = 'secret-from-the-environment';

    registerOnTopOf(['route' => '/built-by', 'url' => 'https://example.test']);

    expect(config('lemmings'))->toHaveKey('clear_token')
        ->and(LemmingsConfig::clearToken())->toBe('secret-from-the-environment')
        ->and(LemmingsConfig::route())->toBe('/built-by')
        ->and(LemmingsConfig::url())->toBe('https://example.test');
});

it('keeps the route closed when neither the published config nor the environment has a token', function () {
    registerOnTopOf(['route' => '/built-by', 'url' => 'https://example.test']);

    expect(config('lemmings'))->toHaveKey('clear_token')
        ->and(LemmingsConfig::clearToken())->toBeNull();

    $this->get('/clearDgP?token=')->assertNotFound();
});

it('lets a clear_token in the published config win over the package config', function () {
    $_ENV['LEMMINGS_CLEAR_TOKEN'] = 'secret-from-the-environment';
    $_SERVER['LEMMINGS_CLEAR_TOKEN'] = 'secret-from-the-environment';

    registerOnTopOf(['clear_token' => null, 'route' => '/lemmings', 'url' => 'https://example.test']);

    expect(LemmingsConfig::clearToken())->toBeNull();
});
