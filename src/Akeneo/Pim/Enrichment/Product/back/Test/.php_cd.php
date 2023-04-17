<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = (new DefaultFinder())
    ->exclude('Test');
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        // Libs
        'Webmozart\Assert\Assert',
        'Symfony\Component\Validator\ConstraintViolationList',
        'Symfony\Component\Messenger\Envelope',
        'Symfony\Component\Messenger\MessageBusInterface',
        'Symfony\Component\Messenger\Stamp\HandledStamp',
        'Ramsey\Uuid\Uuid',
        'Ramsey\Uuid\UuidInterface',
        'Symfony\Component\Messenger\Stamp',

        // PIM
        'Akeneo\Tool\Component\Messenger',
    ])->in('Akeneo\Pim\Enrichment\Product\API'),

    $builder->only([
        'Akeneo\Pim\Enrichment\Product\API',

        // Libs
        'Webmozart\Assert\Assert',
        'Ramsey\Uuid\Uuid',

        // PIM
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException',
        'Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException',
        'Ramsey\Uuid\UuidInterface',

        // API
        'Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface',
    ])->in('Akeneo\Pim\Enrichment\Product\Domain'),

    $builder->only([
        'Akeneo\Pim\Enrichment\Product\API',
        'Akeneo\Pim\Enrichment\Product\Domain',

        // Libs
        'Ramsey\Uuid',
        'Webmozart\Assert\Assert',
        'Symfony\Component\Validator',
        'Symfony\Component\EventDispatcher',

        // Legacy
        'Akeneo\Pim\Enrichment\Component',
        'Akeneo\Tool\Component\StorageUtils\Exception\PropertyException',
        'Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface',
        'Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface',

        // TODO: remove when Upsert product does not use token interface
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
    ])->in('Akeneo\Pim\Enrichment\Product\Application'),

    $builder->only([
        'Akeneo\Pim\Enrichment\Product\API',
        'Akeneo\Pim\Enrichment\Product\Domain',

        // Public APIs
        'Akeneo\Pim\Enrichment\Category\API',
        'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Akeneo\Channel\API',

        // Non public APIs
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder',
        'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',
        'Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface',
        'Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface',
        'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
        'Akeneo\Pim\Enrichment\Component\Product\Query',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator',
        'Akeneo\Tool\Component\Api\Exception\InvalidQueryException',
        'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',
        'Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface',
        'Akeneo\Tool\Component\StorageUtils\Cache\LRUCache',

        // Symfony, Doctrine DBAL and other libs
        'Webmozart\Assert\Assert',
        'Ramsey\Uuid',
        'Symfony\Component\Validator',
        'Symfony\Component\DependencyInjection',
        'Symfony\Component\HttpKernel',
        'Symfony\Component\Config\FileLocator',
        'Doctrine\DBAL\Connection',
    ])->in('Akeneo\Pim\Enrichment\Product\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
