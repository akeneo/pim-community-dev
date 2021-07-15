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
            'GuzzleHttp\ClientInterface',

            // Akeneo common dependencies
            'Akeneo\Platform\Bundle\FeatureFlagBundle',
            'Akeneo\Platform\Bundle\UIBundle',
            'Akeneo\Platform\Bundle\InstallerBundle',
        ]
    )->in('Akeneo\FreeTrial\Infrastructure'),
];

return new Configuration($rules, $finder);
