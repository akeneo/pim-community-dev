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
            'Akeneo\Apps\Application',
            'Akeneo\Apps\Domain',
        ]
    )->in('Akeneo\Apps\Application'),

    $builder->only(
        [
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Doctrine\DBAL\Driver\Connection',
            'Symfony\Component',
        ]
    )->in('Akeneo\Apps\Infrastructure\Install'),

    $builder->only(
        [
            'Symfony\Component',
        ]
    )->in('Akeneo\Apps\Infrastructure\Symfony'),

    $builder->only(
        [
            'Akeneo\Apps\Application\Service\CreateClientInterface',
            'Akeneo\Apps\Domain\Model\ClientId',
            'FOS\OAuthServerBundle\Model\ClientManagerInterface',
            'OAuth2\OAuth2',
        ]
    )->in('Akeneo\Apps\Infrastructure\Client'),
];

$config = new Configuration($rules, $finder);

return $config;
