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
        'Akeneo\Tool',
        'Akeneo\Pim\Enrichment\Component',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Liip\ImagineBundle',
        // TODO the feature use the datagrid
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',
        // TODO: dependencies related to the front end, remove twig screens
        'Twig_SimpleFunction', // used by the category tree
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\ObjectIdentifierType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\TranslatableFieldType',
        'Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',
        // TODO: EASY PICK! it should be registered in the structure bundle
        'Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterAttributeTypePass',

        // TIP-915: PIM/Enrichment should not be linked to AttributeOption
        // TIP-916: Do not check if entities exist in PQB filters or sorters
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',

        // TIP-918: PIM/Enrichment should not be linked to GroupType
        'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface',

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
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        // TODO: : EASY PICK! API PaginatorInterface should catch ServerErrorResponseException and throw its own exception,
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',
        // TODO: we should not inject the UserContext as a service
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        // TODO: use to find the repository of the reference data (often to check if the reference data exists, sometimes we really need more information)
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface',
        // TODO: EASY PICK! move StructureVersion\EventListener\TableCreator to Platform
        'Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents',
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
        // TODO: widget discussion
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',
        // TODO: remove Flash\Message in CategoryTreeController
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message',
        // TODO: used in ProductCommentController to retrieve current locale => frontend or not discussion for "contextual" parameters
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',
    ])->in('Akeneo\Pim\Enrichment\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',

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

        // TIP-922: PIM/Enrichment should not be linked to Currency
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface',

        // TIP-926: Each context should have its own "User"
        // TIP-924: PIM/Enrichment should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-926: Each context should have its own "User"
        // TIP-925: WIP - PIM/Enrichment should not be linked to UserManager
        'Akeneo\UserManagement\Bundle\Manager\UserManager',

        // TIP-927: Move EnsureConsistentAttributeGroupOrderTasklet to Structure
        'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',
        'Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FindAttributeGroupOrdersEqualOrSuperiorTo', //should not use bundle in component

        // TIP-928: PIM/Enrichment should not be linked to AssociationType
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',

        // TIP-929: Extract the Attribute part of the ValidMetricValidator
        // TIP-929: Extract the Attribute part of the ValidMetricValidator
        'Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric',

        // TIP-930: ValueCollectionFactory should not log
        'Psr\Log\LoggerInterface',

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
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        // TIP-939: FILTERS
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',

        // TODO a component should not rely on a bundle
        'Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter',
        'Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasureException',
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager',
        // TODO: EASY PICK! a component should not rely a concrete implementation => use the right repo interface
        'Doctrine\ORM\EntityRepository',
        // TODO it should only be used in the bundle (security, used to check if granted to ACL)
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        // TODO normalization for front end purpose
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        // TODO: StructureVersionProvider discussion, for the cache of the frontend
        'Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface',
        // TODO: do not inject user context as a service
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        // TODO: is ProductMassActionRepositoryInterface still used?
        'Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface',
        // TODO: a component should not use a bundle
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',
        // TODO: should not have a contextual service
        'Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext',
        // TODO: EASY PICK! move JobLauncherInterface to component
        'Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface',
    ])->in('Akeneo\Pim\Enrichment\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
