<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Symfony\Bundle',
        'Symfony\Contracts',
        'Akeneo\Tool',
        'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
        'Akeneo\Pim\Automation\IdentifierGenerator\API',
        'Akeneo\Pim\Enrichment\Component',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent',
        'Akeneo\Platform\Job\Domain\Model\Status',
        'Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Liip\ImagineBundle',
        'Dompdf\Dompdf',
        'Dompdf\Options',
        'Webmozart\Assert\Assert',
        'Psr\Log\LoggerInterface',
        'Ramsey\Uuid',
        // TODO the feature use the datagrid
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\FilterBundle',
        // TODO: dependencies related to the front end, remove twig screens
        'Twig',
        'Akeneo\Pim\Enrichment\Product\API',
        'Akeneo\Pim\Enrichment\Product\Domain\Clock',

        // Event API
        'Akeneo\Platform\Component\EventQueue',
        'Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent',

        'Akeneo\Channel\Infrastructure\Component\Event\ChannelCategoryHasBeenUpdated',

        // TIP-1008: Clean Provider system of Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',

        // TIP-1009: Remove TranslatedLabelsProviderInterface from Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',

        // TIP-1005: Clean UI form types
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType',

        // TODO: EASY PICK! it should be registered in the structure bundle
        'Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass',

        // TIP-915: PIM/Enrichment should not be linked to AttributeOption
        // TIP-916: Do not check if entities exist in PQB filters or sorters
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',

        // TIP-920: PIM/Enrichment should not be linked to Locale
        'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

        // TIP-921: PIM/Enrichment should not be linked to Channel
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',

        // TIP-923: PIM/Enrichment should not be linked to AttributeRequirement
        'Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface',

        // TIP-924 PIM/Enrichment should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface', // ideally, we should have a "Product Contributor"

        // TIP-933: CategoryRepository should not depend on Gedmo
        'Gedmo\Tree\Entity\Repository\NestedTreeRepository',

        //TIP-936: PIM/Enrichment should not be linked to FamilyVariant
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface',

        // TIP-937: PIM/Enrichment should not be linked to Family
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',

        //TIP-938: PIM/Enrichment should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        'Akeneo\Pim\Structure\Bundle\Event\AttributeEvents',

        // TODO: : EASY PICK! API PaginatorInterface should catch ServerErrorResponseException and throw its own exception,
        'Elasticsearch\Common\Exceptions\BadRequest400Exception',
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // TIP-949: Assets should not be Reference Data
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface',

        // TIP-1015: Move TableCreator to Platform
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',

        // TIP-1022: Drop LocaleResolver
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',

        'Elasticsearch\Common\Exceptions\ElasticsearchException',
        'Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister',

        // PIM-10259: Add support for Arabic characters in PDF export
        'ArPHP\I18N\Arabic',

        'Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode',
        'Akeneo\Platform\Bundle\FrameworkBundle\Service\ResilientDeadlockConnection',

        // Category Bounded Context
        'Akeneo\Category\Api', // legit
        'Akeneo\Category\Application\Command\DeleteCategoryCommand\DeleteCategoryCommand',
        'Akeneo\Category\Infrastructure\Component\Model\CategoryInterface',
        'Akeneo\Category\Infrastructure\Component\Model\Category',
        'Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\ItemCategoryRepositoryInterface',
        'Akeneo\Category\Infrastructure\Symfony\Form\CategoryFormViewNormalizerInterface',
        'Akeneo\Category\Domain\Model\Classification\CategoryTree',
        'Akeneo\Category\Domain\Query\GetCategoryInterface',
        'Akeneo\Category\Domain\Query\GetCategoryTreesInterface',
    ])->in('Akeneo\Pim\Enrichment\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Symfony\Contracts',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Doctrine\Persistence',
        'Doctrine\Inflector',
        'Webmozart\Assert\Assert',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Psr\Log\LoggerInterface',
        'Ramsey\Uuid',
        'Akeneo\Pim\Enrichment\Product\API',

        // Event API
        'Akeneo\Platform\Component\EventQueue',
        'Akeneo\Platform\Component\Webhook',

        // Webhook API: event data building
        'Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface',
        'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',

        // Required for NonExistentValuesFilter on channels and locales
        // TODO: there should only be Akeneo\Channel\API exposed
        'Akeneo\Channel\Infrastructure\Component\Query\PublicApi',
        'Akeneo\Channel\API',

        // Required to add quality scores into external API normalized products.
        'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
        'Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query',
        'Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model',

        // TIP-918: PIM/Enrichment should not be linked to GroupType
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',
        'Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface',

        // TIP-920: PIM/Enrichment should not be linked to Locale
        'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

        // TIP-921: PIM/Enrichment should not be linked to Channel
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ActivatedLocale',

        'Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface',

        // TIP-926: Each context should have its own "User"
        // TIP-924: PIM/Enrichment should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-926: Each context should have its own "User"
        // TIP-925: WIP - PIM/Enrichment should not be linked to UserManager
        'Akeneo\UserManagement\Bundle\Manager\UserManager',

        // TIP-927: Move EnsureConsistentAttributeGroupOrderTasklet to Structure
        'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',
        'Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo',

        // TIP-928: PIM/Enrichment should not be linked to AssociationType
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',

        // TIP-929: Extract the Attribute part of the ValidMetricValidator
        // TIP-929: Extract the Attribute part of the ValidMetricValidator
        'Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric',

        // TIP-931: SearchQueryBuilder design problem
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder',
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult',

        // PIM-10832: Remove coupling with indexer and completeness persistence
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer',
        'Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses',

        // TIP-932: KeepOnlyValuesForVariation should use the public API related to the root aggregate Family Variant
        'Akeneo\Pim\Structure\Component\Model\CommonAttributeCollection',

        // TIP-934: AttributeIsAFamilyVariantAxis is part of Structure
        // TIP-935: AddBooleanValuesToNewProductSubscriber design problem
        'Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface',

        //TIP-936: PIM/Enrichment should not be linked to FamilyVariant
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface',

        // TIP-937: PIM/Enrichment should not be linked to Family
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface',

        //TIP-938: PIM/Enrichment should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',

        // TIP-1012: Create a Measure component
        'Akeneo\Tool\Bundle\MeasureBundle',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',

        // TODO it should only be used in the bundle (security, used to check if granted to ACL)
        'Oro\Bundle\SecurityBundle\SecurityFacade',

        // TIP-1008: Clean Provider system of Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // TIP-1034: PIM/Enrichment component should not depend on Oro
        'Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface',

        // TIP-1011: Create a Versioning component
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TIP-929: Extract the Attribute part of the ValidMetricValidator
        'Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric',

        // TIP-1023: Drop CatalogContext
        'Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext',

        'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',
        'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',

        'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',

        // Category Bounded Context
        'Akeneo\Category\Infrastructure\Component\Model\CategoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\ItemCategoryRepositoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\CategoryAwareInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryFilterableRepositoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface',
        'Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\ACLUpdateCategoryUpdatedDate',
    ])->in('Akeneo\Pim\Enrichment\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
