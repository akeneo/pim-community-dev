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
        'Akeneo\Tool',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Oro\Bundle\SecurityBundle\SecurityFacade',
        'Akeneo\Pim\Permission\Component',
        'Symfony\Bundle\FrameworkBundle\Templating\EngineInterface',

        // it implements a CE query differently for permissions
        'Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query',
        'Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory',
        'Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory',
        'Akeneo\Pim\Enrichment\Component\Product\Association\Query\GetAssociatedProductCodesByProduct',

        //TODO: It uses the PQB
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface',
        'Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder',

        // TODO: we should integrate by database instead of using external repository
        'Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TODO: we put everything related to permissions at the same place
        'Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface',
        'Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository',
        'Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobInstanceRepository',
        // TODO: EASY PICK ProductMassActionRepository should not use the publish product repository, is it dead code?
        'Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface',

        //TODO: Link by id instead of reference
        'Akeneo\Asset\Component\Model\CategoryInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\Locale',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface',

        //TODO: It extends Controller
        'Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductPdfController',
        'Akeneo\Pim\Enrichment\Bundle\Controller\Ui\CategoryTreeController',
        'Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductController',
        'Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductModelController',
        'Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface',

        //TODO: It shouldn't rely on model to do join (bounded contexts integration)
        'Akeneo\UserManagement\Component\Model\Group',

        //TODO: Public constants
        'Akeneo\UserManagement\Component\Model\User',
        'Akeneo\Asset\Bundle\AttributeType\AttributeTypes',

        //TODO: It uses jobs (maybe ImportExportBundle is not part of the Platform)
        'Akeneo\Platform\Bundle\ImportExportBundle\Event\JobExecutionEvents',
        'Akeneo\Platform\Bundle\ImportExportBundle\Event\JobProfileEvents',
        'Akeneo\Platform\Bundle\ImportExportBundle\Manager\JobExecutionManager',
        //TODO: we listen to this event to save the permissions
        'Akeneo\Pim\Structure\Bundle\Event\AttributeGroupEvents',
        'Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents',

        //TODO: It hides content for PDF Rendering
        'Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface',
        'Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer',
        'Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererRegistry',
        'Liip\ImagineBundle\Imagine\Cache\CacheManager',
        'Liip\ImagineBundle\Imagine\Data\DataManager',
        'Liip\ImagineBundle\Imagine\Filter\FilterManager',
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\FilterProductValuesHelper',

        //TODO: It uses filters
        'Akeneo\Pim\Enrichment\Bundle\Filter\AbstractFilter',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface',
        'Akeneo\Pim\Enrichment\Bundle\Filter\ProductEditDataFilter',

        //TODO: it overrides Processors => good examples to show what should not be overriden
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddAttributeValueProcessor',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddProductValueProcessor',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditAttributesProcessor',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditCommonAttributesProcessor',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\RemoveProductValueProcessor',
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\UpdateProductValueProcessor',
        'Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable',

        //TODO: Because it overrides normalizer => good examples to show what should not be overriden
        'Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollectionFactory',
        'Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollectionFactory',
        'Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\IncompleteValuesNormalizer',

        //TODO: It extends the writer
        'Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit\ProductAndProductModelWriter',

        //TODO: It uses the datagrid
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\PimFilterBundle',
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\FilterBundle',
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\DatagridViewTypes',

        //TODO: It uses the UI
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message',
        'Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface',
        'Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface',

        //TODO: just because we override ProductFieldsBuilder
        'Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext',
        //TODO: EASY PICK RegisterSerializerPass should go in Tool
        'Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass',
        //TODO: we should not inject the userContext as a service
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        //TODO: just because we override ProductController
        'Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder'
    ])->in('Akeneo\Pim\Permission\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Webmozart\Assert',
        'Doctrine\Common',
        'Akeneo\Tool',

        //TODO: EASY PICK: should be in component
        'Akeneo\Pim\Permission\Bundle\Entity\AssetCategoryAccess',
        'Akeneo\Pim\Permission\Bundle\Entity\ProductCategoryAccess',
        'Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery',

        //TODO: EASY PICK: Interface in component + implementation in bundle
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        //TODO: Move in component + Rework that
        'Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager',
        'Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager',

        //TODO: Link by id instead of reference
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface',

        // TODO: review the voters/filters implementation
        'Akeneo\UserManagement\Component\Model\GroupInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface',

        //TODO: It overrides a default behavior
        'Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent',
        'Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface',
        'Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AbstractFieldSetter',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
    ])->in('Akeneo\Pim\Permission\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
