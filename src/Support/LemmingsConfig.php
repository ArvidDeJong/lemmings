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
     * The secret that opens the maintenance route and the packages page, or null when none is
     * set. An empty value is no token: an empty secret would match an empty `?token=` and open
     * both routes to everyone.
     */
    public static function clearToken(): ?string
    {
        $token = config('lemmings.clear_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * The Blade component the packages page renders in: a layout of the host app such as
     * `layouts.app`, or the plain layout the package ships when nothing is configured.
     */
    public static function layout(): string
    {
        $layout = config('lemmings.layout');

        return is_string($layout) && $layout !== '' ? $layout : 'darvis-lemmings::layout';
    }

    /**
     * The packages the packages page lists, keyed by Composer name, in config order. An entry
     * that misses one of its three strings is left out rather than shown half.
     *
     * @return array<string, array{name: string, purpose: string, url: string}>
     */
    public static function packages(): array
    {
        $packages = config('lemmings.packages');
        $valid = [];

        foreach (is_array($packages) ? $packages : [] as $package => $details) {
            if (! is_string($package) || ! is_array($details)) {
                continue;
            }

            $name = $details['name'] ?? null;
            $purpose = $details['purpose'] ?? null;
            $url = $details['url'] ?? null;

            if (is_string($name) && is_string($purpose) && is_string($url)) {
                $valid[$package] = ['name' => $name, 'purpose' => $purpose, 'url' => $url];
            }
        }

        return $valid;
    }

    /**
     * Path of the packages page: the easter egg path plus `/packages`, so the two move together.
     */
    public static function packagesRoute(): string
    {
        return rtrim(self::route(), '/').'/packages';
    }

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
