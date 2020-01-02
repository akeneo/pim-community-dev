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
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',

            // Dependency on Symfony Validator to ease validation
            'Symfony\Component\Validator',
        ]
    )->in('Akeneo\Connectivity\Connection\Application'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',

            'Akeneo\Tool\Bundle\ApiBundle\Entity\Client',

            'Doctrine\DBAL\Driver\Connection',

            // OAuth server authentication
            'FOS\OAuthServerBundle\Model\ClientManagerInterface',
            'FOS\OAuthServerBundle\Util\Random',
            'OAuth2\OAuth2',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\Client'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Tool\Component\StorageUtils',

            'Akeneo\UserManagement\Bundle\Manager\UserManager',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

            'Doctrine\DBAL\Driver\Connection',

            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\User'),

    $builder->only(
        [
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Doctrine\DBAL\Driver\Connection',
            'Symfony\Component',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\Install'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',

            // Dependency on HTTP foundation for Request/Response
            'Symfony\Component\HttpFoundation',
            // Dependency on constraint violations to correctly display errors on frontend
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            // ACL dependency
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\InternalApi'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain',

            // Dependency on Doctrine DBAL for persistence layer
            'Doctrine\DBAL\Connection',
            // Dependency for uuid generation
            'Ramsey\Uuid\Uuid',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\Persistence'),

    $builder->only(
        [
            'Symfony\Component',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\Symfony'),
];

$config = new Configuration($rules, $finder);

return $config;
