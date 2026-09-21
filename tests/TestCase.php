<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Tests;

use Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // The package calls nothing external. This keeps it that way.
        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [
            DarvisLemmingsProvider::class,
        ];
    }
}
