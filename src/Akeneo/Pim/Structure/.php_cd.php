<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony',
        'Webmozart\Assert\Assert',
        'Akeneo\Tool',
        'Akeneo\Pim\Structure\Component',
        'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Oro\Bundle\DataGridBundle\Event\BuildBefore',
        'Oro\Bundle\FilterBundle\Grid\Extension\Configuration',
        'Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface',
        'Oro\Bundle\FilterBundle\Filter\ChoiceFilter',
        'Oro\Bundle\FilterBundle\Filter\AbstractFilter',
        'Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType',
        'Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface',
        'Psr\Log\LoggerInterface',
        'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
        'Akeneo\Channel\API',
        'Akeneo\Pim\Automation\DataQualityInsights\PublicApi',

        // TIP-906: Functional problem to query products before removing AttributeOption
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory',

        // TIP-907: Functionnal problem we should not create empty associations
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',

        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface',

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-910: PIM/Structure should not be linked to Channel
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',

        // I don't think we should add install subscriber in platform instead of structure (discussed with Arnaud L.)
        'Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents',
    ])->in('Akeneo\Pim\Structure\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Symfony\Contracts',
        'Doctrine\Common',
        'Doctrine\Persistence',
        'Webmozart\Assert\Assert',
        'Akeneo\Tool\Component',

        // TIP-911: ImmutableVariantAxesValidator should not be tied to Doctrine
        'Doctrine\ORM\EntityManagerInterface',

        // TIP-912: AttributeRepositoryInterface should be tied to Doctrine
        'Doctrine\ORM\QueryBuilder',

        // TIP-910: PIM/Structure should not be linked to Channel
        'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',

        // TIP-908: Entities should not be linked directly to Locale for translation purposes
        // TIP-909: PIM/Structure should not be linked to Locale
        'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',

        //TIP-906: Functionnal problem -> used to check if we can remove a family / family variant
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface',

        // Coupling issues:
        // TIP-1021: Mass edit should not be linked to Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor',

        // TIP-907: Functional problem we should not create empty associations
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',

        // TIP-1011: Create a Versioning component
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',

        // TIP-1008: Clean Provider system of Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Filter\FilterProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',

        // TIP-929: Extract the Attribute part of the ValidMetricValidator
        'Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface',
        'Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider',

        // TIP-914 FamilyNormalizer should not use PIM/Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer',

        'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
        'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',

        'Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface',
        'Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\DashboardScoresProjectionRepository'
    ])->in('Akeneo\Pim\Structure\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
