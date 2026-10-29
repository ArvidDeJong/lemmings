<?php

use Composer\InstalledVersions;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * GET /lemmings/packages lists the configured packages and whether Composer installed them. That
 * is information about the application, so the page sits behind the same token as /clearDgP and
 * every refusal is the 404 of a path that does not exist.
 */
it('does not exist for anyone while no token is configured', function () {
    $this->get('/lemmings/packages')->assertNotFound();
    $this->get('/lemmings/packages?token=')->assertNotFound();
    $this->get('/lemmings/packages?token=anything')->assertNotFound();
});

it('answers 404 for a missing or wrong token, exactly like a path that does not exist', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);

    $this->get('/lemmings/packages')->assertNotFound();
    $this->get('/lemmings/packages?token[]=the-right-secret')->assertNotFound();
    $this->withHeaders(['X-Lemmings-Token' => 'wrong'])->get('/lemmings/packages')->assertNotFound();

    $refused = $this->get('/lemmings/packages?token=wrong');
    $unknown = $this->get('/no-such-path-at-all');

    expect($refused->status())->toBe($unknown->status());

    foreach (['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'Retry-After'] as $header) {
        expect($refused->headers->has($header))->toBeFalse($header.' gives the route away');
    }
});

it('shows the configured packages with the right token, installed or not', function () {
    config([
        'lemmings.clear_token' => 'the-right-secret',
        'lemmings.packages' => [
            'example/not-installed' => [
                'name' => 'Not Installed',
                'purpose' => 'Only in the config.',
                'url' => 'https://example.test/not-installed',
            ],
            'laravel/framework' => [
                'name' => 'Laravel',
                'purpose' => 'The framework itself.',
                'url' => 'https://laravel.com',
            ],
        ],
    ]);

    $this->get('/lemmings/packages?token=the-right-secret')
        ->assertOk()
        ->assertViewIs('darvis-lemmings::packages')
        ->assertSee('<a href="https://laravel.com" target="_blank" rel="noopener">Laravel</a>', false)
        ->assertSee('The framework itself.')
        ->assertSee((string) InstalledVersions::getPrettyVersion('laravel/framework'))
        ->assertSee('<a href="https://example.test/not-installed" target="_blank" rel="noopener">Not Installed</a>', false)
        ->assertSee('not installed')
        ->assertSeeInOrder(['class="is-missing"', 'class="is-installed"'], false);
});

it('lists the packages the config file ships, without this package itself', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);

    $packages = array_keys((array) config('lemmings.packages'));

    // Whoever opens the page has this package installed, so listing it would say nothing.
    expect($packages)->not->toContain('darvis/lemmings')->and($packages)->not->toBe([]);

    $response = $this->get('/lemmings/packages?token=the-right-secret')->assertOk();

    foreach ($packages as $package) {
        $response->assertSee($package);
    }
});

it('takes the token from a header too', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);

    $this->withHeaders(['X-Lemmings-Token' => 'the-right-secret'])->get('/lemmings/packages')->assertOk();
});

it('renders in its own plain layout by default, out of search engines', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);

    $this->get('/lemmings/packages?token=the-right-secret')
        ->assertSee('<title>Packages</title>', false)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('renders in the layout component of the host app when one is configured', function () {
    view()->addLocation(dirname(__DIR__).'/views');

    config(['lemmings.clear_token' => 'the-right-secret', 'lemmings.layout' => 'layouts.app']);

    $this->get('/lemmings/packages?token=the-right-secret')
        ->assertOk()
        ->assertSee('<title>Host layout</title>', false)
        ->assertDontSee('<title>Packages</title>', false)
        ->assertSee('darvis/mailtrap');
});

it('shares the limit of five wrong tokens a minute with the maintenance route', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);

    foreach (range(1, 5) as $attempt) {
        $this->get('/lemmings/packages?token=wrong')->assertNotFound();
    }

    $this->get('/lemmings/packages?token=the-right-secret')->assertNotFound();
    $this->get('/clearDgP?token=the-right-secret')->assertNotFound();

    expect(RateLimiter::tooManyAttempts('lemmings-clear:127.0.0.1', 5))->toBeTrue();

    $this->travel(61)->seconds();

    $this->get('/lemmings/packages?token=the-right-secret')->assertOk();
});

it('keeps its name, lives under the easter egg path and answers GET and HEAD only', function () {
    $route = Route::getRoutes()->getByName('lemmings.packages');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('lemmings/packages')
        ->and($route->methods())->toBe(['GET', 'HEAD'])
        ->and($route->gatherMiddleware())->toBe([]);

    $this->post('/lemmings/packages')->assertStatus(405);
});

it('gives way to a host app route on the same path', function () {
    Route::get('/lemmings/packages', fn () => 'mine');

    $this->get('/lemmings/packages')->assertOk()->assertSee('mine');
});
