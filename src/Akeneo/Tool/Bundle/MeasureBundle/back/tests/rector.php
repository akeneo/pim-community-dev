<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->paths([__DIR__ . '/..']);
    $rectorConfig->importShortClasses(false);
    $rectorConfig->skip([JsonThrowOnErrorRector::class]);
};
