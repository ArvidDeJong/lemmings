<?php

namespace Tests\Feature;

use Darvis\Lemmings\Laravel\Providers\DarvisLemmingsProvider;
use Orchestra\Testbench\TestCase;

class LemmingsRouteTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [DarvisLemmingsProvider::class];
    }

    /** @test */
    public function it_can_access_lemmings_page()
    {
        $response = $this->get('/lemmings');

        $response->assertStatus(200);
        $response->assertViewIs('darvis-lemmings::lemmings');
    }

    /** @test */
    public function it_returns_correct_content_type()
    {
        $response = $this->get('/lemmings');

        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
