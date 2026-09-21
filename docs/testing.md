---
title: "Testing"
nav_order: 7
description: "Test darvis/lemmings in your own Laravel application: the easter egg page, your link and a closed /clearDgP, without really clearing the caches of the app."
---

# Testing

The package calls nothing external, so there is nothing to fake. There is one thing to avoid: a request to `/clearDgP` with the right token really runs `cache:clear`, `storage:link` and five other commands in the application under test. Test that the route is closed, and replace the `Artisan` facade when you want to test the open case.

## A complete test

`tests/Feature/LemmingsTest.php`, written for Pest:

```php
<?php

use Illuminate\Support\Facades\Artisan;

it('shows the easter egg page', function () {
    $this->get(route('lemmings'))
        ->assertOk()
        ->assertViewIs('darvis-lemmings::lemmings')
        ->assertSee('Oh no more Lemmings....');
});

it('links the umbrella to our own site', function () {
    config(['lemmings.url' => 'https://your-own-site.example']);

    $this->get(route('lemmings'))
        ->assertSee('href="https://your-own-site.example"', false);
});

it('keeps the maintenance route closed without the token', function () {
    config(['lemmings.clear_token' => 'test-token']);

    $this->get('/clearDgP')->assertNotFound();
    $this->get('/clearDgP?token=wrong')->assertNotFound();
});

it('clears the caches with the token, without running the commands', function () {
    config(['lemmings.clear_token' => 'test-token']);

    $artisan = new class
    {
        public array $called = [];

        public function call(string $command): int
        {
            $this->called[] = $command;

            return 0;
        }
    };

    Artisan::swap($artisan);

    $this->withHeaders(['X-Lemmings-Token' => 'test-token'])
        ->get('/clearDgP')
        ->assertOk()
        ->assertJson(['status' => 'success']);

    expect($artisan->called)->toContain('cache:clear', 'optimize:clear');
});
```

What happens:

- The first two tests render the real view. `assertSee(..., false)` compares the raw HTML, which is needed for the `href`.
- The third test sets a token and sends none, then a wrong one. Both get a 404 and no command runs. Both count as a wrong token, see below.
- The last test puts a small object in place of the `Artisan` facade with `Artisan::swap()`. The route calls that object instead of the real commands, so the caches of your test application stay as they are.

In PHPUnit the same calls work inside a test method of `Tests\TestCase`.

## What you can and cannot set inside a test

| Setting | Inside a test | Why |
| --- | --- | --- |
| `lemmings.url` | `config([...])` works | Read while the page is rendered |
| `lemmings.clear_token` | `config([...])` works | Read on every request |
| `lemmings.route` | too late | Read once, while the application boots |

To test another path, set it before the application boots, in `phpunit.xml`:

```xml
<php>
    <env name="LEMMINGS_ROUTE" value="/built-by"/>
</php>
```

## The limit of five wrong tokens in tests

The route counts wrong and missing tokens for each IP address, which is `127.0.0.1` in a Laravel HTTP test. After five within a minute every request gets a 404, the right token included.

- With the `array` cache store, which the `phpunit.xml` of a new Laravel application sets, every test starts with an empty counter. Only a single test that sends more than five wrong tokens runs into the limit.
- With another cache store the counter survives from one test to the next. Reset it where you need to:

```php
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::clear('lemmings-clear:127.0.0.1');
```

A request with the right token is not counted.
