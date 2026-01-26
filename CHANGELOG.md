# Changelog

All notable changes to the Lemmings package will be documented in this file.

## [1.5.0] - 2026-01-26

### Added

-   Configuration file for customizable URL and route path
-   Config publishing via `php artisan vendor:publish --tag=lemmings-config`
-   Named route `lemmings` for easier referencing
-   Environment variable support (`LEMMINGS_URL`, `LEMMINGS_ROUTE`)

### Changed

-   Documentation translated to English
-   Improved ServiceProvider with config merging

## [1.4.1] - 2025-11-05

### Removed

-   Version field removed from composer.json for better package management

### Changed

-   Improved code formatting in composer.json

## [1.3.0] - 2025-06-26

### Removed

-   Tests removed to improve compatibility
-   Laravel Pint removed to minimize dependencies

## [1.2.0] - 2025-06-26

### Changed

-   Extended Laravel compatibility to Laravel 11 and 12 (`^11.0|^12.0`)

## [1.1.0] - 2024-XX-XX

### Added

-   [Previous changes for version 1.1.0]

## [1.0.1] - 2025-03-06

### Added

-   Support for Laravel 11
-   Compatibility with Livewire 3
-   FluxUI integration

### Changed

-   Minimum PHP version raised to 8.2
-   PHPUnit upgrade to version 11
-   Improved documentation

### Removed

-   Support for Laravel < 11

## [1.0.0] - 2024-01-01

### Added

-   Initial release
-   Basic Lemmings easter egg functionality
-   Laravel auto-discovery support
