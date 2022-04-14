<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->path('tests/Unit');

$builder = new RuleBuilder();

$rules = [
    // Domain should only use classes from Domain
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated',
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\EventQueue\Event',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'PhpSpec\ObjectBehavior',
            'Prophecy\Argument',
            'Symfony\Component\Validator\ConstraintViolation',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain'),

    // Domain should only use classes from same Domain
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Apps',
            'PhpSpec\ObjectBehavior',
            'Symfony\Component\Validator\ConstraintViolation',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Apps'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Audit',
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Audit'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode',
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\ErrorManagement'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\Settings',
            'PhpSpec\ObjectBehavior',
            'Prophecy\Argument',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Settings'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\ValueObject'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\ValueObject',
            'Akeneo\Connectivity\Connection\Domain\Webhook',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved',
            'Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated',
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\EventQueue\Event',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\Webhook'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection'),

    // Application should only use classes from Domain or Application
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application',
            'Akeneo\Connectivity\Connection\Domain',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry',
            'Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker',
            'Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl',
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\EventQueue\BulkEvent',
            'Akeneo\Platform\Component\EventQueue\BulkEventInterface',
            'Akeneo\Platform\Component\EventQueue\Event',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\Webhook\Context',
            'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',
            'Akeneo\Platform\Component\Webhook\EventDataCollection',
            'Akeneo\Platform\VersionProviderInterface',
            'Akeneo\Tool\Bundle\ApiBundle\Entity\Client',
            'Akeneo\UserManagement\Component\Model\GroupInterface',
            'Akeneo\UserManagement\Component\Model\RoleInterface',
            'Akeneo\UserManagement\Component\Model\User',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',
            'GuzzleHttp\Psr7\Response',
            'PhpSpec\ObjectBehavior',
            'PHPUnit\Framework\Assert',
            'Prophecy\Argument',
            'Psr\Log\LoggerInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationListInterface',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Exception\UnexpectedValueException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application'),

    // Application should only use classes from same context in Domain or Application
    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\Apps',
            'Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface',
            'Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface',
            'Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface',
            'Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface',
            'Akeneo\Connectivity\Connection\Domain\Apps',
            'Akeneo\Connectivity\Connection\Domain\ClockInterface',
            'Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface',
            'Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection',
            'Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry',
            'Akeneo\Tool\Bundle\ApiBundle\Entity\Client',
            'Akeneo\UserManagement\Component\Model\GroupInterface',
            'Akeneo\UserManagement\Component\Model\RoleInterface',
            'PhpSpec\ObjectBehavior',
            'Prophecy\Argument',
            'Symfony\Component\Validator\ConstraintViolation',
            'Symfony\Component\Validator\ConstraintViolationList',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Apps'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\Audit',
            'Akeneo\Connectivity\Connection\Domain\Audit',
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Audit'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\ConnectionContextInterface',
            'Akeneo\Connectivity\Connection\Application\ErrorManagement',
            'Akeneo\Connectivity\Connection\Domain\ErrorManagement',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval',
            'Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'FOS\RestBundle\Context\Context',
            'FOS\RestBundle\Serializer\Serializer',
            'PhpSpec\ObjectBehavior',
            'PHPUnit\Framework\Assert',
            'Prophecy\Argument',
            'Symfony\Component\Validator\ConstraintViolationInterface',
            'Symfony\Component\Validator\ConstraintViolationList',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\ErrorManagement'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\Marketplace',
            'Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface',
            'Akeneo\Connectivity\Connection\Domain\Marketplace',
            'Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl',
            'Akeneo\Platform\VersionProviderInterface',
            'PhpSpec\ObjectBehavior',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Marketplace'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\Settings',
            'Akeneo\Connectivity\Connection\Domain\Settings',
            'PhpSpec\ObjectBehavior',
            'Prophecy\Argument',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Settings'),

    $builder->only(
        [
            'Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique',
            'Akeneo\Connectivity\Connection\Application\Webhook',
            'Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType',
            'Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection',
            'Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface',
            'Akeneo\Connectivity\Connection\Domain\ValueObject\Url',
            'Akeneo\Connectivity\Connection\Domain\Webhook',
            'Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\EventSubscriptionLog',
            'Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker',
            'Akeneo\Platform\Component\EventQueue\Author',
            'Akeneo\Platform\Component\EventQueue\BulkEvent',
            'Akeneo\Platform\Component\EventQueue\BulkEventInterface',
            'Akeneo\Platform\Component\EventQueue\Event',
            'Akeneo\Platform\Component\EventQueue\EventInterface',
            'Akeneo\Platform\Component\Webhook\Context',
            'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',
            'Akeneo\Platform\Component\Webhook\EventDataCollection',
            'Akeneo\UserManagement\Component\Model\User',
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'GuzzleHttp\Psr7\Response',
            'PhpSpec\ObjectBehavior',
            'PHPUnit\Framework\Assert',
            'Prophecy\Argument',
            'Psr\Log\LoggerInterface',
            'Symfony\Component\Validator\Constraint',
            'Symfony\Component\Validator\ConstraintValidator',
            'Symfony\Component\Validator\Context\ExecutionContextInterface',
            'Symfony\Component\Validator\Exception\UnexpectedTypeException',
            'Symfony\Component\Validator\Exception\UnexpectedValueException',
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface',
        ]
    )->in('Akeneo\Connectivity\Connection\Application\Webhook'),

    // Infrastructure can use anything, no coupling rules implemented
];

$config = new Configuration($rules, $finder);

return $config;
