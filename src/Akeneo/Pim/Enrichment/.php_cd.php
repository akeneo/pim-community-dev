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
        'Akeneo\Channel\Component\Query\PublicApi',
        'Akeneo\Pim\Enrichment\Component',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Liip\ImagineBundle',
        'Dompdf\Dompdf',
        'Dompdf\Options',
        'Webmozart\Assert\Assert',
        'Psr\Log\LoggerInterface',
        // TODO the feature use the datagrid
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        // TODO: dependencies related to the front end, remove twig screens
        'Twig_SimpleFunction', // used by the category tree
        'Twig_Extension', // used by Twig extensions
        'Twig\Environment', // used by Twig extensions

        // Event API
        'Akeneo\Platform\Component\EventQueue',

        'Akeneo\Channel\Component\Event\ChannelCategoryHasBeenUpdated',

        // TIP-1008: Clean Provider system of Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',

        // TIP-1009: Remove TranslatedLabelsProviderInterface from Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',

        // TIP-1005: Clean UI form types
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',

        // TODO: EASY PICK! it should be registered in the structure bundle
        'Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass',

        // TIP-915: PIM/Enrichment should not be linked to AttributeOption
        // TIP-916: Do not check if entities exist in PQB filters or sorters
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',

        // TIP-918: PIM/Enrichment should not be linked to GroupType
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',

        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',

        // TIP-920: PIM/Enrichment should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TIP-921: PIM/Enrichment should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',

        // TIP-922: PIM/Enrichment should not be linked to Currency
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',

        // TIP-923: PIM/Enrichment should not be linked to AttributeRequirement
        'Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface',

        // TIP-924 PIM/Enrichment should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface', // ideally, we should have a "Product Contributor"

        // TIP-933: CategoryRepository should not depend on Gedmo
        'Gedmo\Tree\Entity\Repository\NestedTreeRepository',

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
        'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',

        // TIP-1022: Drop LocaleResolver
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',

        'Akeneo\Pim\Structure\Component\Query\InternalApi\GetAllBlacklistedAttributeCodesInterface',
    ])->in('Akeneo\Pim\Enrichment\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Symfony\Contracts',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Doctrine\Inflector',
        'Webmozart\Assert\Assert',
        'Akeneo\Pim\Structure\Component\Query\PublicApi',
        'Psr\Log\LoggerInterface',

        // Event API
        'Akeneo\Platform\Component\EventQueue',
        'Akeneo\Platform\Component\Webhook',

        // Webhook API: event data building
        'Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface',
        'Akeneo\Platform\Component\Webhook\EventDataBuilderInterface',

        // Required for NonExistentValuesFilter on channels and locales
        'Akeneo\Channel\Component\Query\PublicApi',

        // Required to add quality scores into external API normalized products.
        'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag',
        'Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQueryInterface',
        'Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection',

        // TIP-915: PIM/Enrichment should not be linked to AttributeOption
        // TIP-916: Do not check if entities exist in PQB filters or sorters
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',

        // TIP-918: PIM/Enrichment should not be linked to GroupType
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',
        'Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface',

        // TIP-920: PIM/Enrichment should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TIP-921: PIM/Enrichment should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Validator\Constraint\ActivatedLocale',

        // TIP-922: PIM/Enrichment should not be linked to Currency
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface',
        'Akeneo\Channel\Component\Model\CurrencyInterface',

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

        // TIP-1033: PIM/Enrichment should not depend on EntityRepository
        'Doctrine\ORM\EntityRepository',

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

        // TIP-1020: Move JobLauncherInterface
        'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
    ])->in('Akeneo\Pim\Enrichment\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
