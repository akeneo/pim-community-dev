<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/releaseV7.rector.php']);

    $paths = [
        __DIR__ . '/../../../src/Akeneo/Pim/Automation/RuleEngine',
        __DIR__ . '/../../../src/Akeneo/Pim/Enrichment',
        __DIR__ . '/../../../src/Akeneo/Tool/Bundle/RuleEngineBundle',
        __DIR__ . '/../../../src/Akeneo/Tool/Component/RuleEngine',
        __DIR__ . '/../../../grth/src/Akeneo/Pim/TableAttribute',
        __DIR__ . '/../../../src/Akeneo/Pim/Automation/DataQualityInsights',

        __DIR__ . '/../../../vendor/akeneo/pim-community-dev/src/Akeneo/Pim',
        __DIR__ . '/../../../vendor/akeneo/pim-community-dev/components/identifier-generator',
    ];

    $rectorConfig->paths($paths);
};
