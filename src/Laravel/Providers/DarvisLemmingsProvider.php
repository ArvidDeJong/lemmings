<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Laravel\Providers;

use Illuminate\Support\ServiceProvider;

class DarvisLemmingsProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'darvis-lemmings');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../../config/lemmings.php' => config_path('lemmings.php'),
            ], 'lemmings-config');
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../../config/lemmings.php',
            'lemmings'
        );
    }
}
