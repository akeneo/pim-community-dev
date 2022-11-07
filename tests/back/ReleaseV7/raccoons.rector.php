<?php

declare(strict_types=1);
use Rector\Caching\ValueObject\Storage\FileCacheStorage;

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/releaseV7.rector.php']);
    $paths = [
        __DIR__ . '/../../../components/job-automation',
        __DIR__ . '/../../../components/tailored-export',
        __DIR__ . '/../../../components/tailored-import',
        __DIR__ . '/../../../config',
        __DIR__ . '/../../../grth/src/Akeneo/Platform/Bundle/MonitoringBundle',
        __DIR__ . '/../../../grth/src/Akeneo/Platform/Bundle/TestBundle',
        __DIR__ . '/../../../src/AcmeEnterprise',
        __DIR__ . '/../../../src/Akeneo/AssetManager',
        __DIR__ . '/../../../src/Akeneo/Platform',
        __DIR__ . '/../../../src/Akeneo/ReferenceEntity',
        __DIR__ . '/../../../src/Akeneo/SharedCatalog',
        __DIR__ . '/../../../src/Akeneo/Tool',
        __DIR__ . '/../../../src/Kernel.php',
        __DIR__ . '/../../../upgrades/',

        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/src/Acme",
        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/src/Akeneo/Platform",
        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/src/Akeneo/Tool",
        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/src/Behat",
        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/src/Kernel.php",
        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/src/Oro",
        __DIR__ . "/../../../vendor/akeneo/pim-community-dev/upgrades",
    ];

    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('./var/cache/rector');
    $rectorConfig->paths($paths);
};
