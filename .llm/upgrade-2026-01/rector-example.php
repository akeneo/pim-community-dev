<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

/**
 * Rector configuration for Akeneo PIM project migration
 * 
 * This configuration should be adapted according to the project's specific needs.
 * Apply rules one by one using individual sets.
 */

return static function (RectorConfig $rectorConfig): void {
    // Paths to analyze
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/upgrades',
    ]);

    // Paths to skip
    $rectorConfig->skip([
        // Ignore existing migration files
        __DIR__ . '/std-build/migration',
        // Ignore vendors
        __DIR__ . '/vendor',
        // Ignore generated files
        __DIR__ . '/var',
    ]);

    // Import PHP sets
    // Apply progressively, one version at a time
    // $rectorConfig->sets([
    //     SetList::PHP_74,
    //     SetList::PHP_80,
    //     SetList::PHP_81,
    //     SetList::PHP_82,
    //     SetList::PHP_83,
    //     SetList::PHP_84, // If available
    //     SetList::PHP_85, // If available
    // ]);

    // Import Symfony sets
    // Apply progressively, one version at a time
    // $rectorConfig->sets([
    //     SymfonySetList::SYMFONY_60,
    //     SymfonySetList::SYMFONY_64,
    //     SymfonySetList::SYMFONY_70,
    //     SymfonySetList::SYMFONY_80, // If available
    // ]);

    // PHP 8.2 specific rules
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SetList::PHP_82,
    // ]);

    // PHP 8.3 specific rules
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SetList::PHP_83,
    // ]);

    // PHP 8.4 specific rules (if available)
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SetList::PHP_84,
    // ]);

    // PHP 8.5 specific rules (if available)
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SetList::PHP_85,
    // ]);

    // Symfony 6.0 specific rules
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SymfonySetList::SYMFONY_60,
    // ]);

    // Symfony 6.4 specific rules
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SymfonySetList::SYMFONY_64,
    // ]);

    // Symfony 7.0 specific rules
    // Uncomment and use progressively
    // $rectorConfig->sets([
    //     SymfonySetList::SYMFONY_70,
    // ]);

    // Symfony 8.0 specific rules (if available)
    // Uncomment and use progressively
    // ⚠️ IMPORTANT: Symfony 8.0 requires PHP 8.4.0 or higher
    // $rectorConfig->sets([
    //     SymfonySetList::SYMFONY_80,
    // ]);

    // PHPStan configuration (optional)
    // $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
};

/**
 * USAGE:
 * 
 * 1. For PHP 8.2:
 *    vendor/bin/rector process --set=PHP_82 --dry-run
 *    vendor/bin/rector process --set=PHP_82
 * 
 * 2. For PHP 8.3:
 *    vendor/bin/rector process --set=PHP_83 --dry-run
 *    vendor/bin/rector process --set=PHP_83
 * 
 * 3. For PHP 8.4 (if available):
 *    vendor/bin/rector process --set=PHP_84 --dry-run
 *    vendor/bin/rector process --set=PHP_84
 * 
 * 4. For PHP 8.5 (if available):
 *    vendor/bin/rector process --set=PHP_85 --dry-run
 *    vendor/bin/rector process --set=PHP_85
 * 
 * 5. For Symfony 6.0:
 *    vendor/bin/rector process --set=SYMFONY_60 --dry-run
 *    vendor/bin/rector process --set=SYMFONY_60
 * 
 * 6. For Symfony 6.4:
 *    vendor/bin/rector process --set=SYMFONY_64 --dry-run
 *    vendor/bin/rector process --set=SYMFONY_64
 * 
 * 7. For Symfony 7.0:
 *    vendor/bin/rector process --set=SYMFONY_70 --dry-run
 *    vendor/bin/rector process --set=SYMFONY_70
 * 
 * 8. For Symfony 8.0 (if available):
 *    ⚠️ IMPORTANT: Verify PHP >= 8.4.0 before
 *    vendor/bin/rector process --set=SYMFONY_80 --dry-run
 *    vendor/bin/rector process --set=SYMFONY_80
 * 
 * IMPORTANT:
 * - Always do a dry-run before applying
 * - Run tests after each rule
 * - Document in tracking files
 * - Symfony 8.0 requires PHP 8.4.0 or higher
 */
