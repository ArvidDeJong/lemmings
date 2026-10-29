<?php

declare(strict_types=1);

namespace Darvis\Lemmings\Support;

use Composer\InstalledVersions;

/**
 * Joins the configured package list with what Composer installed in the host application.
 */
final class Packages
{
    /**
     * One row per configured package, in config order. `version` is null for a package that is
     * not installed, so a view needs no second lookup.
     *
     * @return list<array{installed: bool, name: string, package: string, purpose: string, url: string, version: ?string}>
     */
    public static function status(): array
    {
        $rows = [];

        foreach (LemmingsConfig::packages() as $package => $details) {
            $installed = InstalledVersions::isInstalled($package);

            $rows[] = [
                'installed' => $installed,
                'name' => $details['name'],
                'package' => $package,
                'purpose' => $details['purpose'],
                'url' => $details['url'],
                'version' => $installed ? InstalledVersions::getPrettyVersion($package) : null,
            ];
        }

        return $rows;
    }
}
