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
        ]
    )->in('Akeneo\Platform\CommunicationChannel\Application'),

    $builder->only(
        [
            'Akeneo\Platform\CommunicationChannel\Application',
            'Akeneo\Platform\CommunicationChannel\Domain',
            'Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents',
            'Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface',
            'Akeneo\UserManagement\Bundle\Context\UserContext',
            'Symfony\Component',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\FetchMode',
            'GuzzleHttp\Client'
        ]
    )->in('Akeneo\Platform\CommunicationChannel\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
