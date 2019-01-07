<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$builder = new RuleBuilder();
$finder = new DefaultFinder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Akeneo\Tool',
        'Symfony\Bundle',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Akeneo\Pim\WorkOrganization\Workflow\Component',

        // TIP-979: Remove ServerErrorResponseException
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',

        // TIP-980: Workflow should not be linked to User
        // TIP-982: Rework User/Draft link
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-981: Workflow should not be linked to Asset
        'Akeneo\Asset\Component\Repository\AssetRepositoryInterface',
        'Akeneo\Asset\Component\Model\AssetInterface',
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',

        // TIP-984: Workflow should not be linked to Channel
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TIP-985: Workflow should not be linked to Currency
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',

        // TIP-986: Workflow should not be linked to Locale
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',

        //TODO: It uses the permissions
        'Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException',
        'Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface',
        'Akeneo\Pim\Permission\Component\Authorization\DenyNotGrantedCategorizedEntity',
        'Akeneo\Pim\Permission\Component\Factory\FilteredEntityFactory',

        //TODO: We should not rely on a contextual parameter in constructor and use the UserId instead of username
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\Pim\Permission\Bundle\User\UserContext',

        //TODO: draft should be linked to user by ID (and we should not keep the name of the user in the draft)
        'Akeneo\UserManagement\Component\Event\UserEvent',

        //TODO: should be done in frontend
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',

        //TODO: We should integrate by database instead of using external repository
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',

        // TIP-983: Workflow should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',

        //TODO Link by id instead of reference
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        //TODO: We should rely on type given by a type system and not on public constants
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',
        'Akeneo\Pim\Permission\Component\Attributes',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        //TODO: It uses the PQB
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch',
        'Akeneo\Pim\Enrichment\Bundle\Filter',
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface', //For the reference data filter
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper',
        'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException',
        'Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException',
        'Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions',

        // TODO: Used by twig in the datagrid to create a fake value
        'Akeneo\Pim\Structure\Component\Factory\AttributeFactory',

        // TODO: product commands extended for drafts :/
        'Akeneo\Pim\Enrichment\Bundle\Command\UpdateProductCommand',
        'Akeneo\Pim\Enrichment\Bundle\Command\QueryProductCommand',

        // TIP-987: Published should be less coupled to Product
        // TIP-988: Split Published vs Draft/Proposal
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection',
        'Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductCodesByProduct',
        'Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry',

        // TIP-1004: WidgetInterface located in Platform is used in multiple contexts
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\NotificationBundle',

        // TIP-1014: Do not use custom Flash Messages
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message',

        //TODO: It uses the locale resolver to get the current locale, should be a contextual parameter
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',

        //TODO the feature uses the datagrid
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\PimFilterBundle',

    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Bundle'),
    $builder->only([
        'Doctrine\Common',
        'Symfony\Component',
        'Akeneo\Tool\Component',

        // TIP-1011: Create a Versioning component
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TIP-983: Workflow should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',

        // TODO: a component should not use a bundle
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TODO: createDatagridQueryBuilder method used by datagrid
        'Doctrine\ORM\QueryBuilder',

        // TODO usage of public constant
        'Akeneo\Pim\Permission\Component\Attributes',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        //TODO: relationship between bounded context (query data though repository)
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',

        // TIP-987: Published should be less coupled to Product
        // TIP-988: Split Published vs Draft/Proposal
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface',
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractCompleteness',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProductUniqueData',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor',

        // TIP-980: Workflow should not be linked to User
        // TIP-982: Rework User/Draft link
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-989 Do not use a PIM/Enrichment constraint
        'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString',

        //TODO: It uses the PQB
        'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface',

        //TODO: public constants of formats used to index proposals
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelNormalizer',

        // TIP-990: Move classes to component
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper',
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager',

    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Component'),
];

$config = new Configuration($rules, $finder);

return $config;

