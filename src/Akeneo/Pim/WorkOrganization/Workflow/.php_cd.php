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

        // TODO: EASY PICK! to remove, this exception is never fired
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',

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
        'Akeneo\Asset\Component\Repository\AssetRepositoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        //TODO: EASY PICK! to remove, not used
        'Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface',

        //TODO Link by id instead of reference
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Asset\Component\Model\AssetInterface',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        //TODO: We should rely on type given by a type system and not on public constants
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        'Akeneo\Pim\Permission\Component\Attributes',

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

        // TODO: published products are totally coupled to products
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
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

        //TODO: It uses a widget
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',

        //TODO: It sends notification
        'Akeneo\Platform\Bundle\NotificationBundle',
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

        // TODO: a component should not use a bundle
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager',

        // TODO: createDatagridQueryBuilder method used by datagrid
        'Doctrine\ORM\QueryBuilder',

        // TODO usage of public constant
        'Akeneo\Pim\Permission\Component\Attributes',
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        //TODO: relationship between bounded context (query data though repository)
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',

        // TODO: published products are totally coupled to products
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
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractCompleteness',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProductUniqueData',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor',

        //TODO: used to normalize proposals notifications of the user
        'Akeneo\UserManagement\Component\Model\UserInterface',

        //TODO: EASY PICK! not not use this constraint just a regular string
        'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString',

        //TODO: It uses the PQB
        'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface',

        //TODO: public constants of formats used to index proposals
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelNormalizer',

        //TODO: EASY PICK! move it into the component
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper',
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager',

    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Component'),
];

$config = new Configuration($rules, $finder);

return $config;

