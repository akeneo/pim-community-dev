<?php

declare(strict_types=1);
use Rector\Caching\ValueObject\Storage\FileCacheStorage;

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/releaseV7.rector.php']);
    $paths = [
        __DIR__ . '/../../../components/supplier-portal-retailer',
        __DIR__ . '/../../../components/supplier-portal-supplier',
    ];

    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('./var/cache/rector');
    $rectorConfig->paths($paths);
};
