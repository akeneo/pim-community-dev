<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

/**
 * Rector configuration for Akeneo PIM project migration
 * 
 * This configuration is used for migrating PHP 8.1 → 8.4 → 8.5 and Symfony 5.4 → 8.0
 * Apply rules one by one using individual sets via command line:
 * 
 * PHP 8.2: vendor/bin/rector process --set=PHP_82 --dry-run
 * PHP 8.3: vendor/bin/rector process --set=PHP_83 --dry-run
 * PHP 8.4: vendor/bin/rector process --set=PHP_84 --dry-run
 * PHP 8.5: vendor/bin/rector process --set=PHP_85 --dry-run
 * 
 * Symfony 6.0: vendor/bin/rector process --set=SYMFONY_60 --dry-run
 * Symfony 6.4: vendor/bin/rector process --set=SYMFONY_64 --dry-run
 * Symfony 7.0: vendor/bin/rector process --set=SYMFONY_70 --dry-run
 * Symfony 8.0: vendor/bin/rector process --set=SYMFONY_80 --dry-run
 */

return static function (RectorConfig $rectorConfig): void {
    // Paths to analyze
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/upgrades',
        __DIR__ . '/components',
    ]);

    // Paths to skip
    $rectorConfig->skip([
        // Ignore existing migration files
        __DIR__ . '/std-build/migration',
        // Ignore vendors
        __DIR__ . '/vendor',
        // Ignore generated files
        __DIR__ . '/var',
        // Ignore frontend files
        __DIR__ . '/front-packages',
        __DIR__ . '/frontend',
    ]);

    // Note: Sets are applied via command line --set parameter
    // This allows applying one rule at a time for better control
    // Example: vendor/bin/rector process --set=PHP_82
};
