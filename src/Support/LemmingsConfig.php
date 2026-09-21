<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Support;

/**
 * The one place that reads the package config. Callers ask this class, so a default is written
 * once and a caller cannot quietly disagree with config/lemmings.php about what it is.
 */
final class LemmingsConfig
{
    /**
     * Path the easter egg is served on. The routes file reads it while the provider boots, so
     * with `php artisan route:cache` a new value only counts after the cache is rebuilt.
     */
    public static function route(): string
    {
        $route = config('lemmings.route');

        // An empty path would register the easter egg as the home page of the host app.
        return is_string($route) && $route !== '' ? $route : '/lemmings';
    }

    /**
     * Address the image links to.
     */
    public static function url(): string
    {
        $url = config('lemmings.url');

        return is_string($url) && $url !== '' ? $url : 'https://lemmings.darvis.nl';
    }
}
