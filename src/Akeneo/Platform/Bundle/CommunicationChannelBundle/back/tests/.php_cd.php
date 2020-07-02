<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only(
        [
            'Akeneo\Platform\CommunicationChannel\Domain',
            'Akeneo\Platform\VersionProviderInterface',
        ]
    )->in('Akeneo\Platform\CommunicationChannel\Application'),

    $builder->only(
        [
            'Akeneo\Platform\CommunicationChannel\Application',
            'Akeneo\Platform\CommunicationChannel\Domain',
            'Symfony\Component',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Doctrine\DBAL\Driver\Connection'
        ]
    )->in('Akeneo\Platform\CommunicationChannel\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
