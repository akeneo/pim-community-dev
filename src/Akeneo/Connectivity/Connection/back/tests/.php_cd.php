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
            'Akeneo\Connectivity\Connection\Domain\Audit',

            // Could be in \Audit
            'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod',

            // Not ok
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Audit'),

    $builder->only(
        [
            // Ok
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',

            // Ambigue
            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',

            // ???
            'Akeneo\Connectivity\Connection\Application\ConnectionContextInterface',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',

            // Not ok
            'Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\ErrorManagement'),

    $builder->only(
        [
            // Ok
            'Akeneo\Connectivity\Connection\Domain\Settings',

            // Ambigue
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Settings'),

    $builder->only(
        [
            // Ok
            'Symfony\Component\OptionsResolver\OptionsResolver',

            // Ambigue
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Exception\UnexpectedValueException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Psr\Http\Message\ResponseInterface',
            'Psr\Log\LoggerInterface',

            // Ok
            'Akeneo\Connectivity\Connection\Domain\Clock',
            'Akeneo\Connectivity\Connection\Domain\Webhook',
            'Akeneo\Platform\Component\EventQueue',
            'Akeneo\Platform\Component\Webhook',

            // Not ok
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Doctrine\Common\Persistence\ObjectRepository',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            'Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken',
            'Symfony\Component\Security\Core\User\UserInterface',

            // ???
            'Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException',
            'Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Webhook'),


    $builder->only([
        'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',
        'Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod',

        // Not ok
        'Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType',
    ])->in('Akeneo\Connectivity\Connection\Domain\Audit'),

    $builder->only([
        'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',

        // Not ok
        'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode',
    ])->in('Akeneo\Connectivity\Connection\Domain\ErrorManagement'),

    $builder->only([
        'Symfony\Component\Validator\Context\ExecutionContextInterface',
        'Symfony\Component\Validator\ConstraintViolationInterface',
        'Symfony\Component\Validator\ConstraintViolationListInterface',
    ])->in('Akeneo\Connectivity\Connection\Domain\Settings'),

    $builder->only([
        'Akeneo\Platform\Component\EventQueue\EventInterface',
        'Akeneo\Platform\Component\EventQueue\Author',
        'Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface',

        'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',
        'Akeneo\Connectivity\Connection\Domain\ValueObject\Url',

        'Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated',
        'Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved',
        'Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated',
        'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated',
        'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved',
        'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated',
    ])->in('Akeneo\Connectivity\Connection\Domain\Webhook'),

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
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',

            // Dependency on Encrypter
            'Akeneo\Connectivity\Connection\Infrastructure\Service\Encrypter',

            'Symfony\Component\OptionsResolver\OptionsResolver',
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
