<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Tests;

/**
 * The routes file reads the path while the provider boots, so a test cannot change it afterwards.
 * This test case sets it the way a host app does: before the application boots.
 */
abstract class CustomPathTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('lemmings.route', '/built-by');
        $app['config']->set('lemmings.url', 'https://example.test/?a=1&b="2"');
    }
}
