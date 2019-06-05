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
            // Used for business events
            'Symfony\Component\EventDispatcher\Event',

            // TIP-1017: Do not use public constants of AttributeTypes
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Domain'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',

            // TIP-1017: Do not use public constants of AttributeTypes
            'Akeneo\Pim\Structure\Component\AttributeTypes',

            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Application'),

    $builder->only(
        [
            // External dependencies
            'Guzzle',
            'Symfony\Component\HttpFoundation\Response',
            'Psr\Http\Message\ResponseInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',

            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectLastCompletedFetchProductsExecutionDatetimeQuery',
            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUserIdsAndFamilyCodesWithMissingMappingQuery',

            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification\NotifyUserAboutMissingMapping',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\Batch',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            // TODO: should be removed see with JJ and AL
            'Akeneo\Tool\Bundle\BatchBundle',

            // External dependencies
            'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            'Symfony\Component\Validator\Constraints',

            // TODO relationship between bounded context (query data though repository)
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',
            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client',

            // TIP-1017: Do not use public constants of AttributeTypes
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command',

            // Akeneo external bounded contexts
            'Akeneo\Platform\Bundle\InstallerBundle\Event',

            // External dependencies
            'Symfony\Component\EventDispatcher',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',

            // External dependencies
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel\Exception',
            'Oro\Bundle\SecurityBundle\SecurityFacade',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\StorageUtils',
            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames',
            'Akeneo\Tool\Component\Batch\Job\BatchStatus',

            // External dependencies
            'Doctrine',

            // TODO: link by ID instead of reference
            'Akeneo\Pim\Structure\Component\Model\Family',
            'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',

            'Akeneo\Pim\WorkOrganization\Workflow\Component',
            'Akeneo\Tool\Component\StorageUtils',

            // External dependencies
            'Symfony\Component\EventDispatcher',

            // TODO remove all links by reference
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',

            'Akeneo\Pim\Structure\Component\AttributeTypes',
            'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',
            'Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException',

            // TODO relationship between bounded context (query data though repository)
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',
            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch',

            // External dependencies
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            'Symfony\Component\EventDispatcher\GenericEvent',

            // Akeneo external bounded contexts
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',
            'Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent',
            'Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface',
            'Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface',

            'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',

            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',

            'Akeneo\Channel\Component\Model\ChannelInterface',
            'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',
            'Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames',

            // Akeneo external bounded contexts
            'Akeneo\Tool',

            // external dependencies
            'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass',
            // TODO: the next line could be removed with lazy-loaded commands
            'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
            'Symfony\Component',

            // TODO remove all links by reference
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

            // TIP-1017: Do not use public constants of AttributeTypes
            'Akeneo\Pim\Structure\Component\AttributeTypes',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',
            'Akeneo\Pim\Automation\FranklinInsights\Application',

            'Oro\Bundle\DataGridBundle',
            'Oro\Bundle\FilterBundle',
            'Oro\Bundle\PimFilterBundle',

            // TODO relationship between bounded context
            'Akeneo\Pim\Enrichment\Component\Product\Grid',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',

            'Akeneo\Tool\Component\StorageUtils',

            // External dependencies
            'Elasticsearch\Client',
            'Elasticsearch\ClientBuilder',
            'Symfony\Component\Serializer\Normalizer',

            // TODO relationship between bounded context
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter',
            'Akeneo\Pim\Enrichment\Component\Product\Query\Filter',
            'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch'),

    $builder->only(
        [
            'Akeneo\Pim\Automation\FranklinInsights\Domain',

            'Akeneo\Tool\Component\StorageUtils',

            // TODO relationship between bounded context
            'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',
            'Akeneo\UserManagement\Component\Model\UserInterface',
        ]
    )->in('Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification'),
];

$config = new Configuration($rules, $finder);

return $config;
