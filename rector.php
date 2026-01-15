<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/components',
    ]);

    // Skip vendor, tests, and other non-source directories
    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/var',
        __DIR__ . '/tests',
        __DIR__ . '/node_modules',
        '*/tests/*',
        '*/spec/*',
        '*/Test/*',
        '*/Tests/*',
        '*/Spec/*',
        '*Test.php',
        '*Spec.php',
    ]);

    // PHP 8.3 preparation
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
    ]);
};
