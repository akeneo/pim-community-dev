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

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\Pim\Permission\Bundle\User\UserContext',

        // TIP-982: Rework User/Draft link
        'Akeneo\UserManagement\Component\Event\UserEvent',

        // TIP-1008: Clean Provider system of Platform
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',

        // TIP-980: Workflow should not be linked to User
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

        // TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct',

        // TODO: permission
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Permission\Component\Attributes',

        // TIP-983: Workflow should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Factory\AttributeFactory',

        // TIP-987: Published should be less coupled to Product
        // TIP-988: Split Published vs Draft/Proposal
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        // TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch',
        'Akeneo\Pim\Enrichment\Bundle\Filter',
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
        'Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductIdsQueryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\DescendantProductModelIdsQueryInterface',

        // TIP-949: Assets should not be Reference Data
        // TIP-963: Define the Products public API
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface', // For the reference data PQB filter

        // TIP-1031: Do not override PIM/Enrichment commands
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

        // TIP-1022: Drop LocaleResolver
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

        // TIP-1011: Create a Versioning component
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TODO: createDatagridQueryBuilder method used by datagrid
        'Doctrine\ORM\QueryBuilder',

        // TODO: permission
        'Akeneo\Pim\Permission\Component\Attributes',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        //TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface',
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

        // TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface',

        // TIP-1032: Workflow should not depend on PIM/Enrichment normalizers
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

