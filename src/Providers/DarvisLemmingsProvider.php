<?php

namespace Darvis\Lemmings\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;

class DarvisLemmingsProvider extends ServiceProvider
{


    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {


        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'darvis-lemmings');

        // \Livewire\Livewire::component('lemmings', \Darvis\Lemmings\App\Http\Livewire\Lemmings::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}
