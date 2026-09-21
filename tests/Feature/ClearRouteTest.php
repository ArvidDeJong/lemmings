<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/**
 * GET /clearDgP clears the caches for a site owner on hosting without a shell. From 1.5.0 to 1.6.0
 * it was open to every visitor. It now only works with the secret from LEMMINGS_CLEAR_TOKEN, and
 * without a secret it does not exist. The commands are replaced by a stand-in: running them would
 * clear the caches of the Testbench app and write a storage link into vendor/.
 */
function fakeArtisan(): object
{
    // Testbench's console kernel is final, so the facade cannot mock it. Swap in a stand-in.
    $kernel = new class
    {
        /** @var array<int, string> */
        public array $called = [];

        public function call(string $command): int
        {
            $this->called[] = $command;

            return 0;
        }
    };

    Artisan::swap($kernel);

    return $kernel;
}

it('does not exist for anyone while no token is configured', function () {
    $kernel = fakeArtisan();

    $this->get('/clearDgP')->assertNotFound();
    $this->get('/clearDgP?token=')->assertNotFound();
    $this->get('/clearDgP?token=anything')->assertNotFound();

    expect($kernel->called)->toBe([]);
});

it('treats an empty token in the config as no token', function (mixed $configured) {
    config(['lemmings.clear_token' => $configured]);
    $kernel = fakeArtisan();

    $this->get('/clearDgP?token=')->assertNotFound();
    $this->get('/clearDgP')->assertNotFound();

    expect($kernel->called)->toBe([]);
})->with(['empty string' => [''], 'null' => [null], 'not a string' => [123]]);

it('answers 404 for a missing or wrong token, so it does not give away that it is there', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);
    $kernel = fakeArtisan();

    $this->get('/clearDgP')->assertNotFound();
    $this->get('/clearDgP?token=wrong')->assertNotFound();
    $this->get('/clearDgP?token[]=the-right-secret')->assertNotFound();
    $this->withHeaders(['X-Lemmings-Token' => 'wrong'])->get('/clearDgP')->assertNotFound();

    expect($kernel->called)->toBe([]);
});

it('clears the caches, recreates the storage link and answers with JSON for the right token', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);
    $kernel = fakeArtisan();

    $this->get('/clearDgP?token=the-right-secret')
        ->assertOk()
        ->assertExactJson([
            'status' => 'success',
            'message' => 'All caches have been cleared and storage link recreated.',
        ]);

    expect($kernel->called)->toBe([
        'cache:clear',
        'route:clear',
        'config:clear',
        'view:clear',
        'storage:link',
        'event:clear',
        'optimize:clear',
    ]);
});

it('takes the token from a header too, which keeps it out of the access log', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);
    $kernel = fakeArtisan();

    $this->withHeaders(['X-Lemmings-Token' => 'the-right-secret'])->get('/clearDgP')->assertOk();

    expect($kernel->called)->toHaveCount(7);
});

it('limits how often the route can be tried', function () {
    config(['lemmings.clear_token' => 'the-right-secret']);
    fakeArtisan();

    foreach (range(1, 5) as $attempt) {
        $this->get('/clearDgP?token=wrong')->assertNotFound();
    }

    $this->get('/clearDgP?token=the-right-secret')->assertStatus(429);
});

it('keeps its name and path', function () {
    $route = Route::getRoutes()->getByName('lemmings.clear');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('clearDgP')
        ->and($route->methods())->toBe(['GET', 'HEAD']);
});

it('gives way to a host app route on the same path', function () {
    // Host app routes are registered after the package routes, and the last route on a path wins.
    Route::get('/clearDgP', fn () => 'mine');

    $this->get('/clearDgP')->assertOk()->assertSee('mine');
});
