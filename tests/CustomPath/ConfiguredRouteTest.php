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
