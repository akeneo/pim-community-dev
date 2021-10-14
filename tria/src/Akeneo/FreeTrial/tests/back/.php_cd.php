<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Akeneo\FreeTrial',

            // External dependencies
            'Symfony\Component',
            'Psr\Log\LoggerInterface',
            'Psr\Http\Message',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\FetchMode',
            'GuzzleHttp\ClientInterface',
            'GuzzleHttp\Psr7\Response',

            // Akeneo common dependencies
            'Akeneo\Platform\Bundle\FeatureFlagBundle',
            'Akeneo\Platform\Bundle\UIBundle',
            'Akeneo\Platform\Bundle\InstallerBundle',
            'Akeneo\UserManagement\Component\Model\UserInterface',

            // Dependencies for the catalog installation
            'Akeneo\Pim\ApiClient',
            'Akeneo\Tool',
            'Akeneo\UserManagement\Component',
            'Akeneo\Pim\Enrichment\Component',
            'Akeneo\Pim\Automation\DataQualityInsights',
            'Akeneo\Connectivity\Connection',
            'League\Flysystem\FilesystemInterface',
            'League\Flysystem\MountManager',
        ]
    )->in('Akeneo\FreeTrial\Infrastructure'),
];

return new Configuration($rules, $finder);
