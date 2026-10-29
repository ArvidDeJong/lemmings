<?php

it('serves the easter egg on the configured path', function () {
    $this->get('/built-by')->assertOk()->assertViewIs('darvis-lemmings::lemmings');

    expect(route('lemmings', [], false))->toBe('/built-by');
});

it('no longer answers on the default path', function () {
    $this->get('/lemmings')->assertNotFound();
});

it('escapes the configured url in the link', function () {
    $this->get('/built-by')
        ->assertSee('href="https://example.test/?a=1&amp;b=&quot;2&quot;"', false)
        ->assertDontSee('b="2"', false);
});

it('moves the packages page along with the easter egg', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);

    $this->get('/built-by/packages?token=the-right-secret')->assertOk()->assertViewIs('darvis-lemmings::packages');
    $this->get('/lemmings/packages?token=the-right-secret')->assertNotFound();

    expect(route('lemmings.packages', [], false))->toBe('/built-by/packages');
});
