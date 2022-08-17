<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Set\ValueObject\SetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_80,
    ]);

    $rectorConfig->paths([__DIR__ . '/../../back/Domain', __DIR__ . '/../../back/Infrastructure']);
    $rectorConfig->importShortClasses(false);
    $rectorConfig->skip([JsonThrowOnErrorRector::class]);
};

