# 2.2.0-ALPHA1 (2018-02-21)

## Bug fixes

- GITHUB-7641: Fix bug related to product export

## Enhancements

- PIM-7106: Display the 1st variant product created as product model image
- PIM-6334: Add support of product model to the export builder
- PIM-6329: The family variant is now removable from the UI

## BC breaks

### Classes

- PIM-6334: Removal of class `Pim\Component\Connector\Processor\Normalization\ProductModelProcessor`
- PIM-6334: Removal of class `Pim\Component\Connector\Reader\Database\ProductModelReader`

### Constructors

- PIM-6334: Change the constructor of `Pim\Component\Catalog\Normalizer\Standard\ProductModelNormalizer` to add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`

## BC breaks

### Constructors

- Change the constructor of `Pim\Component\Connector\Processor\Denormalization\Product` to remove last `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
- Change the constructor of `Pim\Component\Catalog\EntityWithFamilyVariant` to remove the `Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct` dependency.

### Classes

- Remove last argument of method `fromFlatData` in `Pim\Component\Connector\Processor\Denormalization\Product\FindProductToImport`
- Remove class `Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct`
- Remove class `Pim\Bundle\CatalogBundle\EventSubscriber\AddParentAProductSubscriber`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\ConvertProductToVariantProduct`

### Services and parameters

- Remove service `pim_catalog.builder.variant_product`
- Remove parameter `pim_catalog.entity.variant_product.class`
- Remove service `pim_catalog.entity_with_family.create_variant_product_from_product`

## Deprecations

- Deprecate interface `Pim\Component\Catalog\Model\VariantProductInterface`. Please use `Pim\Component\Catalog\Model\ProductInterface::isVariant()` to determine is a product is variant or not.

# 2.2.0-ALPHA0 (2018-02-13)

## Enhancements

- GITHUB-6943: Update the Docker compose template to run Elasticsearch container in development mode (Thanks [aaa2000](https://github.com/aaa2000)!)
- GITHUB-7538: Add symfony/thanks Composer plugin

## Bug fixes

- GITHUB-7365: Reference Data Collection doesn't load when attached Entity has multiple cardinalities (Thanks Schwierig!)

## BC breaks

### Classes

- PIM-6367: Rename `Pim\Bundle\EnrichBundle\ProductQueryBuilder\MassEditProductAndProductModelQueryBuilder` into `Pim\Component\Catalog\Query\ProductAndProductModelQueryBuilder`
- PIM-6367: Rename `Pim\Component\Catalog\Updater\ProductPropertyAdder` into `Pim\Component\Catalog\Updater\PropertyAdder`
- PIM-6367: Rename `Pim\Component\Catalog\Updater\ProductPropertyRemover` into `Pim\Component\Catalog\Updater\PropertyRemover`
- PIM-6367: Rename `Pim\Component\Catalog\Updater\ProductPropertyCopier` into `Pim\Component\Catalog\Updater\PropertyCopier`
- PIM-6367: Move `Pim\Bundle\EnrichBundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory` to `Pim\Bundle\CatalogBundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory`
- PIM-6367: Move `Pim\Bundle\EnrichBundle\Elasticsearch\CursorFactory` to `Pim\Bundle\CatalogBundle\Elasticsearch\CursorFactory`
- PIM-6367: Move `Pim\Bundle\EnrichBundle\Elasticsearch\Cursor` to `Pim\Bundle\CatalogBundle\Elasticsearch\Cursor`
- PIM-6367: Move `Pim\Bundle\EnrichBundle\Elasticsearch\AbstractCursor` to `Pim\Bundle\CatalogBundle\Elasticsearch\AbstractCursor`
- PIM-6367: Move `Pim\Bundle\EnrichBundle\Elasticsearch\IdentifierResults` to `Pim\Bundle\CatalogBundle\Elasticsearch\IdentifierResults`
- PIM-6367: Move `Pim\Bundle\EnrichBundle\Elasticsearch\IdentifierResult` to `Pim\Bundle\CatalogBundle\Elasticsearch\IdentifierResult`

### Constructors

- PIM-6367: Change the constructor of `Pim\Component\Connector\Processor\Normalization\ProductProcessor` to add `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- PIM-6367: Change the constructor of `Pim\Component\Connector\Writer\Database\ProductModelDescendantsWriter` to remove `Pim\Component\Catalog\Builder\ProductBuilderInterface`
    and to add `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- PIM-6367: Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\ProductDatasource` to add `Pim\Bundle\DataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber`
- PIM-6367: Change the constructor of `Pim\Component\Catalog\Builder` to remove `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface`
- Change the constructor of `Pim\Bundle\CatalogBundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber` to add `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` and `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`

### Services and parameters

- PIM-6367: Rename service `pim_enrich.query.product_and_product_model_query_builder_factory` into `pim_catalog.query.product_and_product_model_query_builder_factory`
- PIM-6367: Rename service `pim_enrich.query.product_and_product_model_query_builder_factory.with_product_and_product_model_cursor` into `pim_catalog.query.product_and_product_model_query_builder_factory.with_product_and_product_model_cursor`
- PIM-6367: Rename service `pim_enrich.factory.product_and_product_model_cursor` into `pim_catalog.factory.product_and_product_model_cursor`
- PIM-6367: Rename service `pim_catalog.updater.product_property_adder` into `pim_catalog.updater.property_adder`
- PIM-6367: Rename service `pim_catalog.updater.product_property_remover` into `pim_catalog.updater.property_remover`
- PIM-6367: Rename service `pim_catalog.updaterproduct_.property_copier` into `pim_catalog.updater.property_copier`
- PIM-6367: Rename class parameter `pim_enrich.query.elasticsearch.product_and_model_query_builder_factory.class` into `pim_catalog.query.elasticsearch.product_and_model_query_builder_factory.class`
- PIM-6367: Rename class parameter `pim_enrich.query.mass_edit_product_and_product_model_query_builder.class` into `pim_catalog.query.product_and_product_model_query_builder.class`
- PIM-6367: Rename class parameter `pim_enrich.elasticsearch.cursor_factory.class` into `pim_catalog.elasticsearch.cursor_factory.class`
- PIM-6367: Rename class parameter `pim_catalog.updater.product_property_adder.class` into `pim_catalog.updater.property_adder.class`
- PIM-6367: Rename class parameter `pim_catalog.updater.product_property_remover.class` into `pim_catalog.updater.property_remover.class`
- PIM-6367: Rename class parameter `pim_catalog.updater.product_property_copier.class` into `pim_catalog.updater.property_copier.class`
- PIM-6367: Remove argument `pim_catalog.resolver.attribute_values` from service `pim_catalog.builder.product`
- PIM-6367: Remove argument `pim_catalog.resolver.attribute_values` from service `pim_catalog.builder.variant_product`

### Interfaces

- Add method `Akeneo\Component\Batch\Job\JobRepositoryInterface::addWarning`
- PIM-7165: Add method `Pim\Component\Catalog\Model\FamilyInterface::getLevelForAttributeCode`
