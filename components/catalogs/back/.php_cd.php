<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    // Domain layer should only use classes from itself and models from the ServiceAPI
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\ServiceAPI\Model',
        ]
    )->in('Akeneo\Catalogs\Domain'),

    // Application layer should only use classes from Domain, ServiceAPI or itself
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
            'Akeneo\Catalogs\ServiceAPI\Model',
            'Akeneo\Catalogs\ServiceAPI\Command',
            'Akeneo\Catalogs\ServiceAPI\Query',
        ]
    )->in('Akeneo\Catalogs\Application'),

    // Infrastructure layer can use anything, but we track used dependencies anyway to detect changes
    $builder->only(
        [
            'Akeneo\Catalogs\ServiceAPI',
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
            'Akeneo\Catalogs\Infrastructure',

            'Symfony\Component\Config',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\EventDispatcher',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Messenger',
            'Symfony\Component\Security',
            'Symfony\Component\Serializer',
            'Doctrine\DBAL',
            'Ramsey\Uuid\Uuid',
            'Akeneo\Platform\Bundle\InstallerBundle',
            'Akeneo\Tool\Component\Api',
            'Akeneo\UserManagement\Component\Model\UserInterface',

            // @todo remove
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
        ]
    )->in('Akeneo\Catalogs\Infrastructure'),

    // ServiceAPI layer should only use classes from itself or symfony/messenger
    $builder->only(
        [
            'Akeneo\Catalogs\ServiceAPI',

            // Constraints as Attributes
            'Symfony\Component\Validator\Constraints',
            // Message Bus
            'Symfony\Component\Messenger',
        ]
    )->in('Akeneo\Catalogs\ServiceAPI'),
];

$config = new Configuration($rules, $finder);

return $config;
