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

            // Dependency on Symfony Validator to ease validation
            'Symfony\Component\Validator',
        ]
    )->in('Akeneo\Apps\Application'),

    $builder->only(
        [
            'Akeneo\Apps\Application\Service\CreateClientInterface',
            'Akeneo\Apps\Domain\Model\ValueObject\ClientId',
            'FOS\OAuthServerBundle\Model\ClientManagerInterface',
            'OAuth2\OAuth2',
        ]
    )->in('Akeneo\Apps\Infrastructure\Client'),

    $builder->only(
        [
            'Akeneo\Apps\Application\Service\CreateUserInterface',
            'Akeneo\Apps\Domain\Model\ValueObject\UserId',
            'Akeneo\Tool\Component\StorageUtils',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ]
    )->in('Akeneo\Apps\Infrastructure\User'),

    $builder->only(
        [
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Doctrine\DBAL\Driver\Connection',
            'Symfony\Component',
        ]
    )->in('Akeneo\Apps\Infrastructure\Install'),

    $builder->only(
        [
            'Akeneo\Apps\Application',
            'Akeneo\Apps\Domain',

            // Dependency on HTTP foundation for Request/Response
            'Symfony\Component\HttpFoundation',
            // Dependency on constraint violations to correctly display errors on frontend
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            // ACL dependency
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
        ]
    )->in('Akeneo\Apps\Infrastructure\InternalApi'),

    $builder->only(
        [
            'Akeneo\Apps\Domain',

            // Dependency on Doctrine DBAL for persistence layer
            'Doctrine\DBAL\Connection',
            // Dependency for uuid generation
            'Ramsey\Uuid\Uuid',
        ]
    )->in('Akeneo\Apps\Infrastructure\Persistence'),

    $builder->only(
        [
            'Symfony\Component',
        ]
    )->in('Akeneo\Apps\Infrastructure\Symfony'),
];

$config = new Configuration($rules, $finder);

return $config;
