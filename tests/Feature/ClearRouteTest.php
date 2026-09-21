<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/**
 * The package has registered GET /clearDgP since 1.0. These tests pin what it does today, so a
 * change to it is a deliberate one. The commands are mocked: running them would clear the caches
 * of the Testbench app and write a storage link into vendor/.
 */
it('registers the maintenance route under a fixed path, without middleware', function () {
    $route = Route::getRoutes()->getByName('lemmings.clear');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('clearDgP')
        ->and($route->methods())->toBe(['GET', 'HEAD'])
        ->and($route->gatherMiddleware())->toBe([]);
});

it('clears the caches, recreates the storage link and answers with JSON', function () {
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

    $this->get('/clearDgP')
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
