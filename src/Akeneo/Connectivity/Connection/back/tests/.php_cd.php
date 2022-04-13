<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath([
    'tests/CatalogBuilder',
    'tests/EndToEnd',
    'tests/Integration',
    'tests/PhpStan',
]);

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

            // Unit tests
            'Akeneo\Platform\Component\EventQueue\Event',
            'PhpSpec\ObjectBehavior',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Prophecy\Argument',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
            'Symfony\Component\Validator\ConstraintViolation',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain'),

    // Domain should only use classes from same Domain
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Apps',

            // Exceptions
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',

            // Unit tests
            'PhpSpec\ObjectBehavior',
            'Symfony\Component\Validator\ConstraintViolation',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Apps'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Audit',

            // Exceptions
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType',

            // Unit tests
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Audit'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement',

            // Exceptions
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode',

            // Unit tests
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\ErrorManagement'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Settings',

            // Exceptions
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',

            // Unit tests
            'PhpSpec\ObjectBehavior',
            'Prophecy\Argument',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Settings'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ValueObject',

            // Unit tests
            'PhpSpec\ObjectBehavior',
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

            // Unit tests
            'Akeneo\Platform\Component\EventQueue\Event',
            'PhpSpec\ObjectBehavior',
            'Akeneo\UserManagement\Component\Model\UserInterface',

        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Webhook'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection',

            // Unit tests
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection'),

    // Application should only use classes from Domain or Application
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Connectivity\Connection\Application',

            // Exceptions
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

            'Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl',
            'Akeneo\Platform\Component\EventQueue\BulkEventInterface',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\Webhook\Context',
            'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',
            'Akeneo\Platform\Component\Webhook\EventDataCollection',
            'Akeneo\Platform\Component\EventQueue\BulkEvent',
            'Akeneo\Platform\VersionProviderInterface',

            'Psr\Log\LoggerInterface',
            'Psr\Http\Message\ResponseInterface',

            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',

            // Unit tests
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\EventQueue\Event',
            'Akeneo\UserManagement\Component\Model\User',
            'PhpSpec\ObjectBehavior',
            'PHPUnit\Framework\Assert',
            'Prophecy\Argument',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker',
            'GuzzleHttp\Psr7\Response',
            'Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
            'Akeneo\Tool\Bundle\ApiBundle\Entity\Client',
            'Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
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

            'Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface',
            'Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface',
            'Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface',
            'Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface',

            'Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry',

            'Akeneo\UserManagement\Component\Model\RoleInterface',

            // Unit tests
            'PhpSpec\ObjectBehavior',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User',
            'Akeneo\Tool\Bundle\ApiBundle\Entity\Client',
            'Akeneo\UserManagement\Component\Model\GroupInterface',
            'Prophecy\Argument',
            'Symfony\Component\Validator\ConstraintViolation',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId',
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

            // Unit tests
            'PhpSpec\ObjectBehavior',
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes',
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

            // Unit tests
            'PhpSpec\ObjectBehavior',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection',
            'Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'PHPUnit\Framework\Assert',
            'Prophecy\Argument',
            'Symfony\Component\Validator\ConstraintViolationList',

        ]
    )->in('Akeneo\Connectivity\Connection\Application\ErrorManagement'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Marketplace',
            'Akeneo\Connectivity\Connection\Application\Marketplace',

            // Exceptions
            'Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface',

            'Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl',
            'Akeneo\Platform\VersionProviderInterface',

            // Unit tests
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Marketplace'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Settings',
            'Akeneo\Connectivity\Connection\Application\Settings',

            // Exceptions
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',

            // Unit tests
            'PhpSpec\ObjectBehavior',
            'Prophecy\Argument',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
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

            // Unit tests
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\EventQueue\Event',
            'Akeneo\UserManagement\Component\Model\User',
            'PhpSpec\ObjectBehavior',
            'PHPUnit\Framework\Assert',
            'Prophecy\Argument',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\Url',
            'GuzzleHttp\Psr7\Response',
            'Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection',
            'Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique'
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Webhook'),

    // Infrastructure can use anything, no coupling rules implemented
];

$config = new Configuration($rules, $finder);

return $config;
