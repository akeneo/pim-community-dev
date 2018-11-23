<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$builder = new RuleBuilder();
$finder = new DefaultFinder();

$rules = [
    $builder->only([
        //It used the Channel Component --> Channel is allowed
        //TODO Link by id instead of reference
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        //TODO See how to deal with assets
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',
        'Akeneo\Asset\Component\Model\AssetInterface',
        'Akeneo\Asset\Component\Repository\AssetRepositoryInterface',

        //It uses the Enrichment Commponent --> Enrichment is allowed
        //TODO Link by id instead of reference and see what to do to avoid the bundle intercoupling
        'Akeneo\Pim\Enrichment\Bundle\Command',
        'Akeneo\Pim\Enrichment\Bundle\Doctrine',
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch',
        'Akeneo\Pim\Enrichment\Bundle\Filter',
        'Akeneo\Pim\Enrichment\Component\Product',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        //It uses the permissions
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository', //TODO Repo should belong to component
        'Akeneo\Pim\Permission\Bundle\User\UserContext', //TODO Kind of a god object
        'Akeneo\Pim\Permission\Component',

        //It uses the StructureComponent --> Structure is allowed
        'Akeneo\Pim\Structure\Component\AttributeTypes', // Used by the presenters
        'Akeneo\Pim\Structure\Component\Factory\AttributeFactory', // Used by twig in the datagrid

        //TODO Link by id instead of reference
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface', //For the reference data filter
        'Akeneo\Pim\Structure\Component\Repository',

        //It uses the Workflow component
        'Akeneo\Pim\WorkOrganization\Workflow\Component',

        //It uses the UI
        'Akeneo\Platform\Bundle\DashboardBundle',
        'Akeneo\Platform\Bundle\NotificationBundle',
        'Akeneo\Platform\Bundle\UIBundle',

        //Tool used by the workflow
        'Akeneo\Tool',
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        'Akeneo\UserManagement\Component\Event\UserEvent',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\UserManagement\Component\Repository\UserRepositoryInterface',

        //NORMAL COUPLING
        'Doctrine',
        'Elasticsearch\Common\Exceptions\ServerErrorResponseException',

        //It uses the datagrid it should not use Oro directly but Pim as an ACL
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\PimFilterBundle',
        'Oro\Bundle\SecurityBundle',

        //TODO Lazy load command
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Templating\EngineInterface',
        'Symfony\Component',

    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Bundle'),
    $builder->only([
        'Doctrine',
        'Symfony\Component',

        // It depends on Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Repository\AssociationRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface', // TODO Make a copy of this repository?
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface', // TODO Make a copy of this repository ?
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation', // TODO published product should have its own representation of an association
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface', // TODO Link by id
        'Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString',
        'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface', // TODO A Published Product Builder
        'Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractCompleteness',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProductUniqueData',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelNormalizer',

        // It depends on Permissions
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository', //TODO Repository should belong to Component
        'Akeneo\Pim\Permission\Component',

        // It depends on Structure
        // TODO Link by Id
        'Akeneo\Pim\Structure\Component\AttributeTypes', // TODO Used only for constants we should rely on the type system
        'Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',
        'Akeneo\Pim\Structure\Component\Repository', //Todo make an ACL inside our component to not rely on a format

        // It depends on the User for Ui purpose
        'Akeneo\UserManagement\Component\Model\UserInterface', //Todo Link By ID

        // It uses the Workflow bundle
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper', //TODO move it into the component and the associated collection filter
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager', //TODO Move it in component

        // It uses Tools Component
        'Akeneo\Tool', //TODO Introduce an interface for the version manager in component
    ])->in('Akeneo\Pim\WorkOrganization\Workflow\Component'),
];

$config = new Configuration($rules, $finder);

return $config;

