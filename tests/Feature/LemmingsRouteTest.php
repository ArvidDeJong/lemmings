<?php

use Illuminate\Support\Facades\Route;

it('serves the easter egg on /lemmings', function () {
    $this->get('/lemmings')
        ->assertOk()
        ->assertViewIs('darvis-lemmings::lemmings')
        ->assertSee('<title>Oh no more Lemmings....</title>', false);
});

it('names the route lemmings', function () {
    expect(route('lemmings', [], false))->toBe('/lemmings');
});

it('links the image to the configured url', function () {
    $this->get('/lemmings')->assertSee('href="https://lemmings.darvis.nl"', false);
});

it('keeps the page out of search engines', function () {
    $this->get('/lemmings')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('answers GET and HEAD only', function () {
    expect(Route::getRoutes()->getByName('lemmings')->methods())->toBe(['GET', 'HEAD']);

    $this->post('/lemmings')->assertStatus(405);
});

it('runs without any middleware, so without a session or a cookie', function () {
    expect(Route::getRoutes()->getByName('lemmings')->gatherMiddleware())->toBe([]);

    expect($this->get('/lemmings')->headers->getCookies())->toBe([]);
});

it('puts nothing from the request on the page', function () {
    $plain = $this->get('/lemmings')->getContent();

    $probed = $this
        ->withHeaders([
            'User-Agent' => '<script>alert(1)</script>',
            'Referer' => 'https://evil.test/"><script>alert(2)</script>',
            'X-Forwarded-Host' => 'evil.test',
        ])
        ->get('/lemmings?url=javascript:alert(3)&q="><script>alert(4)</script>')
        ->getContent();

    expect($probed)->toBe($plain);
});

it('reveals no versions, environment, debug state or paths', function () {
    config(['app.debug' => true]);

    $html = (string) $this->get('/lemmings')->getContent();

    // The image is one long base64 string in which any short word can turn up by chance.
    $html = (string) preg_replace('/src="data:image\/gif;base64,[^"]+"/', 'src=""', $html);

    expect($html)
        ->not->toContain(app()->version())
        ->not->toContain(PHP_VERSION)
        ->not->toContain(app()->environment())
        ->not->toContain(base_path())
        ->not->toContain('debug');
});
