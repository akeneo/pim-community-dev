<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    // Domain should only use classes from Domain
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain',

            // Exceptions
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',

            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface',

            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated',

            'Webmozart\Assert\Assert',
            'Ramsey\Uuid\Uuid',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain'),

    // Domain should only use classes from same Domain
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Apps',

            // Exceptions
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Apps'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Audit',

            // Exceptions
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Audit'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement',

            // Exceptions
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode',
            'Ramsey\Uuid\Uuid'
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\ErrorManagement'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Settings',

            // Exceptions
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Settings'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\ValueObject'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Webhook',

            // Exceptions
            'Akeneo\Connectivity\Connection\Domain\ValueObject',

            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface',

            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated',

            'Webmozart\Assert\Assert',

        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Webhook'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection'),

    // Application should only use classes from Domain or Application
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Connectivity\Connection\Application',

            // Exceptions
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Exception\UnexpectedValueException',
            'Symfony\Component\OptionsResolver\OptionsResolver',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            'Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken',
            'Symfony\Component\Security\Core\User\UserInterface',

            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface',

            'Akeneo\UserManagement\Component\Model\GroupInterface',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Model\RoleInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',
            'Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface',

            'Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl',
            'Akeneo\Platform\Component\EventQueue\BulkEventInterface',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\Webhook\Context',
            'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',
            'Akeneo\Platform\Component\Webhook\EventDataCollection',
            'Akeneo\Platform\Component\EventQueue\BulkEvent',
            'Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface',

            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',

            'Psr\Log\LoggerInterface',
            'Psr\Http\Message\ResponseInterface',

            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',

            'Ramsey\Uuid\Uuid',

            'Generator',
        ]
    )->in('Akeneo\Connectivity\Connection\Application'),

    // Application should only use classes from same context in Domain or Application
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Apps',
            'Akeneo\Connectivity\Connection\Application\Apps',

            // Exceptions
            'Symfony\Component\Validator\Validator\ValidatorInterface',

            'Akeneo\Connectivity\Connection\Domain\ClockInterface',

            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',
            'Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQueryInterface',
            'Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface',

            'Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App',
            'Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface',

            'Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface',
            'Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface',
            'Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface',

            'Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry',

            'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
            'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',

            'Akeneo\UserManagement\Component\Model\RoleInterface',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Apps'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Audit',
            'Akeneo\Connectivity\Connection\Application\Audit',

            // Exceptions
            'Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',

            'Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Audit'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement',
            'Akeneo\Connectivity\Connection\Application\ErrorManagement',

            // Exceptions
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',

            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',

            'Akeneo\Connectivity\Connection\Application\ConnectionContextInterface',

            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\ErrorManagement'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Marketplace',
            'Akeneo\Connectivity\Connection\Application\Marketplace',

            // Exceptions

            'Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl',
            'Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Marketplace'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\CustomApps',
            'Akeneo\Connectivity\Connection\Application\CustomApps',

            // Exceptions
            'Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\CustomApps'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Settings',
            'Akeneo\Connectivity\Connection\Application\Settings',
            'Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface',

            // Exceptions
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Settings'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\User',

            // Exceptions
            'Akeneo\UserManagement\Component\Model\GroupInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\User'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Webhook',
            'Akeneo\Connectivity\Connection\Application\Webhook',

            // Exceptions
            'Symfony\Component\OptionsResolver\OptionsResolver',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            'Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken',
            'Symfony\Component\Security\Core\User\UserInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Exception\UnexpectedValueException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',

            'Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException',
            'Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface',
            'Akeneo\Connectivity\Connection\Domain\ClockInterface',

            'Akeneo\Platform\Component\EventQueue\BulkEventInterface',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\EventQueue\BulkEvent',
            'Akeneo\Platform\Component\Webhook\Context',
            'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',
            'Akeneo\Platform\Component\Webhook\EventDataCollection',

            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

            'Psr\Log\LoggerInterface',
            'Psr\Http\Message\ResponseInterface',

            'Ramsey\Uuid\Uuid',

            'Generator',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Webhook'),

    // Infrastructure can use anything, no coupling rules implemented
];

$config = new Configuration($rules, $finder);

return $config;
