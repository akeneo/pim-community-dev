# 2.2.x

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
- PIM-7163: Add `Pim\Bundle\UserBundle\Entity\UserInterface::setPhone` and `Pim\Bundle\UserBundle\Entity\UserInterface::getPhone`
