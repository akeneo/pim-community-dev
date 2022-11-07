<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/releaseV7.rector.php']);

    $relativePaths = [
        'src/Akeneo/Pim/Automation/DataQualityInsights',
        'src/Akeneo/Pim/Permission',
        'src/Akeneo/Pim/Structure',
        'src/Akeneo/Pim/WorkOrganization',
        'src/Akeneo/Category',
        'src/Akeneo/Channel',
        'tria/src',
        'tria/upgrades',
        'grth/src/Akeneo/Platform/Bundle/AuthenticationBundle',
        'grth/src/Akeneo/Platform/Component/Authentication',
        'vendor/akeneo/pim-community-dev/src/Akeneo/Category',
        'vendor/akeneo/pim-community-dev/src/Akeneo/Channel',
        'vendor/akeneo/pim-community-dev/src/Akeneo/FreeTrial',
        'vendor/akeneo/pim-community-dev/src/Akeneo/UserManagement',
    ];

    $absolutePaths = array_map(
        static fn (string $relativePath) => sprintf('%s%s%s', __DIR__, '/../../../', $relativePath),
        $relativePaths,
    );

    $rectorConfig->paths($absolutePaths);
};
