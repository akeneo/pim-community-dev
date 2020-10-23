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
            'Akeneo\Connectivity\Connection\Domain',

            // Dependency on Symfony Validator to ease validation
            'Symfony\Component\Validator',
            'Symfony\Component\Security',

            'Doctrine\Common\Persistence\ObjectRepository',

            'Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',

            // TODO: Fix serializer usage to not depends on FOSRestBundle inside Application.
            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',

            'Psr\Log\LoggerInterface',

            'Akeneo\Platform\Component\EventQueue',
            'Akeneo\Platform\Component\Webhook',
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

            // For acceptance tests purpose
            'Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\Client'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Tool\Component\StorageUtils',

            'Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository',
            'Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository',
            'Akeneo\UserManagement\Bundle\Manager\UserManager',
            'Akeneo\UserManagement\Component\Model\GroupInterface',
            'Akeneo\UserManagement\Component\Model\RoleInterface',
            'Akeneo\UserManagement\Component\Model\User',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

            // For acceptance tests purpose
            'Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository',
            'Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryUserPermissionsRepository',

            'Doctrine\DBAL\Driver\Connection',

            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\User'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Pim\Enrichment\Component\FileStorage',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent',
            'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
            'Akeneo\Tool\Component\FileStorage\File\FileStorerInterface',
            'Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface',
            'Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface',
            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',
            'Akeneo\UserManagement\Component\Model\GroupInterface',
            'Akeneo\UserManagement\Component\Model\RoleInterface',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Doctrine\DBAL\Connection',
            'Doctrine\DBAL\Types\Types',
            'OAuth2\OAuth2',
            'Symfony\Component',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\Install'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Connectivity\Connection\Infrastructure\Audit\AggregateAuditData',

            // Dependency on HTTP foundation for Request/Response
            'Symfony\Component\HttpFoundation',
            // Dependency on constraint violations to correctly display errors on frontend
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            // ACL dependency
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',
            'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
            // Dependency to retrieve the current User (and his timezone).
            'Akeneo\UserManagement\Bundle\Context\UserContext',
        ]
    )->in('Akeneo\Connectivity\Connection\Infrastructure\InternalApi'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\UserManagement\Component\Model\User',

            // Dependency for uuid generation
            'Ramsey\Uuid\Uuid',

            // Dependency on Doctrine DBAL for persistence layer
            'Doctrine\DBAL',

            // Dependency on Elasticsearch
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client'
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
