<?php

declare(strict_types=1);

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    // Domain layer should only use classes from itself or constraints annotations
    $builder->only(
        [
            'Akeneo\Catalogs\Domain',

            // Constraints as Attributes
            'Akeneo\Catalogs\Infrastructure\Validation',
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
            'Akeneo\Catalogs\ServiceAPI\Exception',
            'Symfony\Component\Routing\RouterInterface',
            'Ramsey\Uuid\Uuid',
            'Ramsey\Uuid\UuidInterface',
        ]
    )->in('Akeneo\Catalogs\Application'),

    // Infrastructure layer can use anything, but we track used dependencies anyway to detect changes
    $builder->only(
        [
            'Akeneo\Catalogs\ServiceAPI',
            'Akeneo\Catalogs\Domain',
            'Akeneo\Catalogs\Application',
            'Akeneo\Catalogs\Infrastructure',

            // Allowed dependencies in Infrastructure
            'Symfony\Component\Config',
            'Symfony\Component\Console',
            'Symfony\Component\DependencyInjection',
            'Symfony\Component\EventDispatcher',
            'Symfony\Component\HttpFoundation',
            'Symfony\Component\HttpKernel',
            'Symfony\Component\Messenger',
            'Symfony\Component\Routing',
            'Symfony\Component\Security',
            'Symfony\Component\Serializer',
            'Symfony\Component\Validator',
            'Doctrine\DBAL',
            'Ramsey\Uuid\Uuid',
            'League\Flysystem\Filesystem',
            'Opis\JsonSchema',
            'Psr\Log\LoggerInterface',
            'Akeneo\Platform\Bundle\InstallerBundle',
            'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
            'Akeneo\Tool\Component\Api',
            'Akeneo\Connectivity\Connection\ServiceApi',
            'Akeneo\Tool\Bundle\MeasureBundle\ServiceApi',
            'Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException',
            'Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent', // For data flow monitoring
            'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',

            /**********************************************************************************************************/
            /* Below are dependencies that we have, but we shouldn't rely on them.
            /* They are coupling exceptions that should be replaced by better alternatives, like ServiceAPIs.
            /**********************************************************************************************************/

            // This class is not clearly identified as public API
            'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',

            // used in GetCurrentUsernameTrait
            'Akeneo\UserManagement\Component\Model\UserInterface',
            'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

            // used in TemporaryEnrichmentBridge
            'Akeneo\Tool\Bundle\ElasticsearchBundle\Client',
            'Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface',
            'Symfony\Component\OptionsResolver',

            // used in Persistence\Attribute
            'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
            'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
            'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
            'Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface',

            // used in Persistence\Catalog\Product
            'Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer',
            'Akeneo\Pim\Enrichment\Component\Product\Query',
            'Akeneo\Pim\Enrichment\Bundle\Elasticsearch',
            'Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductUuids',

            // used in Persistence\Category
            'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface',
            'Akeneo\Category\Infrastructure\Component\Model\CategoryInterface',
            'Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface',
            'Doctrine\Common\Collections\Collection',
            'Akeneo\Category\Api\FindCategoryTrees',
            'Akeneo\Category\Api\CategoryTree',

            // used in Persistence\Channel
            'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
            'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',

            // used in Persistence\Currency
            'Akeneo\Channel\Infrastructure\Component\Repository\CurrencyRepositoryInterface',

            // used in Persistence\Locale
            'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',
            'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

            // used in Persistence\Family
            'Akeneo\Pim\Structure\Component\Model\FamilyInterface',

            // used in EventSubscriber\CurrencyDeactivationSubscriber
            'Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface',
            'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
            'Akeneo\Tool\Component\Batch\Model\JobInstance',
            'Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface',
            'Akeneo\Tool\Component\StorageUtils\StorageEvents',

            //used in Job\DisableCatalogsOnCurrencyDeactivationConstraint
            'Akeneo\Tool\Component\Batch\Job\JobInterface',
            'Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface',

            //used in Job\DisableCatalogsOnCurrencyDeactivationTasklet
            'Akeneo\Tool\Component\Batch\Model\StepExecution',
            'Akeneo\Tool\Component\Connector\Step\TaskletInterface',
        ]
    )->in('Akeneo\Catalogs\Infrastructure'),

    // ServiceAPI layer should only use classes from itself, constraints annotations or symfony/messenger
    $builder->only(
        [
            'Akeneo\Catalogs\ServiceAPI',

            // Constraints as Attributes
            'Symfony\Component\Validator\Constraints',
            'Akeneo\Catalogs\Infrastructure\Validation',

            // Message Bus
            'Symfony\Component\Messenger',
        ]
    )->in('Akeneo\Catalogs\ServiceAPI'),
];

$config = new Configuration($rules, $finder);

return $config;
