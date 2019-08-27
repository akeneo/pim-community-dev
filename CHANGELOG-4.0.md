# 4.0.x

# Bug fixes

- GITHUB-10247: Fix regex to compile the frontend assets. Thanks @liamjtoohey!

# Technical Improvements

- TIP-1185: Use a single index "product_and_product_model_index" to search on product and product models, instead dedicated product/product model indexes

## BC breaks

### Elasticsearch

- Remove akeneo_pim_product and akeneo_pim_product_model ES indexes and merge into akeneo_pim_product_and_product_model.

### Doctrine mapping

- The entity `Akeneo\Pim\Enrichment\Component\Product\Model\Completeness` is removed, and is no more an association of the `Product` entity.
  The methods `getCompletenesses` and `setCompletenesses` of a `Product` are removed.
  If you want to access the Completeness of products, please use the dedicated class `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`.

### Codebase

- Remove class `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`
- Remove service `pim_catalog.manager.completeness`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface`
- Remove service `pim_catalog.completeness.generator`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface` replace by the implementation `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator`
- Remove method `calculate` from `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\PropertiesNormalizer`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelNormalizer`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelPropertiesNormalizer`
- ProductQueryBuilder` should be used to search products and `ProductModelQueryBuilder` should be used to search product models.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\IdFilter` to add `$prefix`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` to remove `$productClient`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer` to remove `$productClient` and `$productModelClient`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory` to add:
    - `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface`
    - `Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderWithSearchAggregatorFactory` to add:
    - `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface`
    - `Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber` to remove `$productClient` and `$productModelClient`.
- Rename `Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` as `Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener`
- Rename `Akeneo\UserManagement\Bundle\EventListener\UserPreferencesSubscriber` as `Akeneo\UserManagement\Bundle\EventListener\UserPreferencesListener`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\LoadEntityWithValuesSubscriber` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\WriteValueCollectionFactory`
- Change constructor of `Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
    - `Doctrine\Common\Persistence\ObjectRepository`
- Change constructor of `Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager`
    - `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
    - `Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface`
    - `Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext`
- Change constructor of `Akeneo\UserManagement\Bundle\EventListener\UserPreferencesSubscriber` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface`
    - `Akeneo\Channel\Component\Repository\ChannelRepositoryInterface`
    - `Akeneo\Channel\Component\Repository\LocaleRepositoryInterface`
    - `Akeneo\UserManagement\Component\Repository\UserRepositoryInterface`
- Change constructor of `Oro\Bundle\DataGridBundle\Datagrid\Manager` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Oro\Bundle\DataGridBundle\Datagrid\Builder`
    - `Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface`
    - `Oro\Bundle\DataGridBundle\Datagrid\Builder\RequestParameters`
- Change constructor of `Oro\Bundle\DataGridBundle\Datagrid\MetadataParser` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add `Symfony\Component\HttpKernel\Fragment\FragmentHandler`
- Change constructor of `Oro\Bundle\PimDataGridBundle\Twig\FilterExtension` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Oro\Bundle\DataGridBundle\Datagrid\Manager`
    - `Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
    - `Symfony\Component\Translation\TranslatorInterface`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\SequentialEditActionHandler`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\ExportMassActionHandler`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\DeleteProductsMassActionHandler`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\DeleteMassActionHandler`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediator`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\WindowMassAction`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassAction`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductModelDescendantsSaver`, to remove
    `Doctrine\Common\Persistence\ObjectManager`,
    `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager` and
    `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`, and add
    `Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver`, to remove `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`
- Delete `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CompletenessNormalizer` (use `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessNormalizer` instead)
- Delete `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CompletenessCollectionNormalizer` (use `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessCollectionNormalizer` instead)
- Delete `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\CompletenessCollectionNormalizer` (use `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductCompletenessCollectionNormalizer` instead)
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\EntityWithFamilyVariantNormalizer` to add
     `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessWithMissingAttributeCodesCollectionNormalizer`,
     `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator` and remove
     `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface` and `Symfony\Component\Serializer\Normalizer\NormalizerInterface` 
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer` to remove
    `Doctrine\Common\Persistence\ObjectManager`, `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`, `Akeneo\Channel\Component\Repository\ChannelRepositoryInterface`,
    `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface`and `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface` and `Symfony\Component\Serializer\Normalizer\NormalizerInterface, and to add
    `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessCollectionWithMissingAttributeCodesNormalizer` and `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator`
- Change constructor of `Oro\Bundle\PimDataGridBundle\Normalizer\ProductAssociationNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Change constructor of `Oro\Bundle\PimDataGridBundle\Normalizer\ProductNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Removed the following class and the corresponding command:
    - `Akeneo\Pim\Enrichment\Bundle\Command\CreateProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\GetProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\QueryProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\RemoveProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\RemoveWrongBooleanValuesOnVariantProductsBatchCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\RemoveWrongBooleanValuesOnVariantProductsCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\UpdateProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\ValidateObjectsCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\ValidateProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\AnalyzeProductCsvCommand`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Command\PurgeCompletenessCommand`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Command\PurgeProductsCompletenessCommand`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\CompletenessRemover`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\CalculateCompletenessCommand` to remove
    `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface` and add
    `Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses` and
    `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexerInterface` and
    it does not extend `Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand` anymore
- Update constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CompleteFilter`, remove `Doctrine\ORM\EntityManagerInterface` and add `Doctrine\DBAL\Connection`
- Remove methods `getCompletenesses` and `setCompletenesses` from `Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface`
- Replace `Akeneo\Pim\Enrichment\Component\Product\Factory\Write` by `Akeneo\Pim\Enrichment\Component\Product\Factory\Read` with method `createByCheckingData`
- Replace methods and following interface from `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` by the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` and its new methods:
    - `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`
    - `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
- Change constructor of `Akeneo\Platform\Bundle\UIBundle\Imagine\FlysystemLoader` to make `Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\MetricNormalizer` to make `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer` and `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController` to make `Akeneo\Tool\Bundle\ElasticsearchBundle\Client` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController` to make `Akeneo\Tool\Bundle\ElasticsearchBundle\Client` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer` to make `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` mandatory and `string $customFont` becomes the last argument
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxisValidator` to make `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetValuesOfSiblings` mandatory and remove `Akeneo\Pim\Enrichment\Component\Product\Repository\EntityWithFamilyVariantRepositoryInterface`
- Replace methods and following interface from `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` by the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` and its new methods:
- Replace interfaces from `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` by the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` and change methods accordingly. Replaced interfaces are:
    - `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`
    - `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
- Class `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` now implements the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` instead of
    `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface` and `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
- Update constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductsSubscriber` to remove
    `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface` and `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface`
    and add `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface`
- Command `Akeneo\Pim\Enrichment\Bundle\Command\IndexProductCommand` does not extend `Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand` anymore

### CLI Commands

The following CLI commands have been deleted:
- pim:product:create
- pim:product:get
- pim:product:query
- pim:product:remove
- pim:product:validate
- pim:product:update
- pim:catalog:remove-wrong-boolean-values-on-variant-products-batch
- pim:catalog:remove-wrong-boolean-values-on-variant-products
- pim:objects:validate
- pim:connector:analyzer:csv-products
- pim:completeness:purge
- pim:completeness:purge-products

If you want to purge the completeness in order to recalculate it, please use the dedicated command `pim:completeness:calculate`

### Services

- Update `pim_catalog.factory.product_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_identifier_cursor_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_identifier_cursor_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_model_identifier_cursor_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Remove `akeneo_elasticsearch.client.product` from `pim_catalog.elasticsearch.indexer.product`
- Remove `akeneo_elasticsearch.client.product` and `akeneo_elasticsearch.client.product_model` from `pim_catalog.elasticsearch.indexer.product_model`
- Update `akeneo.pim.enrichment.follow_up.completeness_widget_query` to use `akeneo_elasticsearch.client.product_and_product_model`
- Remove `akeneo_elasticsearch.client.product` and `akeneo_elasticsearch.client.product_model` from `pim_catalog.event_subscriber.category.update_indexes_on_category_deletion`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.query.product_model_query_builder_search_after_size_factory_external_api` to use `Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder`
- Update `pim_catalog.query.product_model_query_builder_from_size_factory_external_api` to use `Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder`
- Update `pim_catalog.query.product_model_query_builder_factory` to use `pim_catalog.query.product_model_query_builder`
- Update `pim_catalog.query.product_model_query_builder_from_size_factory` to use `pim_catalog.query.product_model_query_builder`
- Update `pim_catalog.query.product_model_query_builder_search_after_size_factory` to use `pim_catalog.query.product_model_query_builder`
- Remove `pim_catalog.query.product_and_product_model_query_builder_factory.with_product_and_product_model_cursor`
- Update `akeneo.pim.enrichment.query.product_query_builder_from_size_factory.with_product_identifier_cursor` to use `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory`
- Update `pim_enrich.query.product_query_builder_from_size_factory.with_product_and_product_model_from_size_cursor` to use `pim_catalog.query.elasticsearch.product_and_model_query_builder_factory`
- Update `pim_enrich.query.product_query_sequential_edit_builder_factory` to use `pim_catalog.query.product_and_product_model_query_builder.class`
- Rename `pim_versioning.event_subscriber.addversion` to `pim_versioning.event_listener.addversion`
- Rename `pim_user.event_listener.user_preferences` to `pim_user.event_subscriber.user_preferences`
- Remove `pim_enrich.normalizer.completeness` (use `pim_enrich.normalizer.product_completeness` instead)
- Remove `pim_enrich.normalizer.completeness_collection` (use `pim_enrich.normalizer.product_completeness_collection` instead)
- Remove `pim_catalog.normalizer.indexing_product.product.completeness_collection` (use `pim_catalog.normalizer.indexing_product.product.product_completeness_collection` instead)
- Update service `pim_pdf_generator.renderer.product_pdf` to use `pim_catalog.repository.cached_attribute_option` as the 9th argument and `pim_pdf_generator_font` as the 10th
- Update service `pim_catalog.validator.constraint.unique_variant_axes` to remove `pim_catalog.repository.entity_with_family_variant`
- Remove duplicated service definitions for `pim_catalog.validator.constraint.family_variant_axes` and `pim_catalog.validator.constraint.immutable_family_variant_axes` in `src/Akeneo/Pim/Enrichment/Bundle/Resources/cofig/validators.yml`