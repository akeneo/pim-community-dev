<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_80,
    ]);

    $rectorConfig->paths([__DIR__.'/..']);
    $rectorConfig->importShortClasses(false);
    $rectorConfig->importNames();
    $rectorConfig->skip([JsonThrowOnErrorRector::class, SimplifyEmptyCheckOnEmptyArrayRector::class]);
};
