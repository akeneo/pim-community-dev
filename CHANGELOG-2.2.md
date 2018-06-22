# 2.2.10 (2018-06-22)

## Bug fixes

- PIM-7442: Bulk actions ALL - count not taking variant products into account

# 2.2.9 (2018-06-14)

## Bug fixes

- PIM-7399: Fix attributes order on Product model export
- PIM-7384: Fix Memory leak on Quick export
- PIM-7438: Fix usage of identifier attribute on association grid

# 2.2.8 (2018-06-07)

## Bug fixes

- PIM-7316: Fix overlap of boolean fields on product edit form
- PIM-7319: Fix association display on product edit form when managing the association type permissions
- PIM-7393: Improve error message when importing fields without locale or scope specification
- PIM-7382: Fix scopable attributes disappearing from edit form after editing a product model
- PIM-7386: Fix 'NOT IN' operator not taking empty values into account for select fields

# 2.2.7 (2018-05-31)

## Bug fixes

- PIM-7358: Cascade remove variant attribute sets when removing a family variant
- PIM-7367: Fix association of a product and product model with the same identifier
- PIM-7388: Completeness filter on product grid for models does not work as expected
- PIM-7315: Fix 500 on "IS EMPTY" operator for the SKU filter

## BC breaks

- MySQL table constraints have changed. Please execute the pending migrations using the `doctrine:migrations:migrate` console command.

# 2.2.6 (2018-05-24)

## Bug fixes

- PIM-7363: fix the pim:catalog:remove-wrong-boolean-values-on-variant-products command

# 2.2.5 (2018-05-16)

## Bug fixes

- PIM-7305: Fix memory leak on purge job command

# 2.2.4 (2018-04-26)

## Bug fixes

- PIM-7281: Fix inappropriate calls to the cache clearer

## Improvements

- PIM-7310: Fix completeness filter to have the operators '=', '!=', '<', '>' for the product and product model query builder

# 2.2.3 (2018-04-12)

## BC Breaks

- Rename command `pim:catalog:remove-wrong-values-on-variant-products` to `pim:catalog:remove-wrong-boolean-values-on-variant-products` (but an alias is still here so calling it from the old name will still work)

# 2.2.2 (2018-03-29)

## BC Breaks

- Rename `Akeneo\Component\StorageUtils\Cache\CacheClearerInterface` to `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`

## Improve Julia's experience

- AOB-101: Apply user timezone on dates in the UI (missing cases)

## Bug fixes

- PIM-7263: Create a purging command (`pim:catalog:remove-wrong-values-on-variant-products`) for boolean values on variant products that should belong to parents

# 2.2.1 (2018-03-22)

# 2.2.0 (2018-03-21)

# 2.2.0-BETA1 (2018-03-21)

## Improve Julia's experience

- PIM-7097: Add sticky behaviour to product edit form
- PIM-7097: Change the loading image
- PIM-7112: Add lock display on images/assets when user has no edit right
- AOB-99: Add a timezone field to a user
- AOB-100: Apply user timezone on dates in the UI

## Better manage products with variants

- PIM-7090: Add completeness filter on product model export builder
- PIM-7091: Build exports for products models according to their codes
- PIM-7143: Be able to delete products and product models in mass using a backend job
- PIM-6803: Message when delete a family with family variant

## BC breaks

### Interfaces

- AOB-99: Add method `getTimezone` and `setTimezone` to `Pim\Bundle\UserBundle\Entity\UserInterface`
- PIM-7163: Add `Pim\Bundle\UserBundle\Entity\UserInterface::setPhone` and `Pim\Bundle\UserBundle\Entity\UserInterface::getPhone`

### Constructors

- AOB-97: Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- AOB-97: Change the constructor of `Akeneo\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher` to add `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- AOB-100: Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\VersioningController` to add `Pim\Bundle\UserBundle\Context\UserContext`
- AOB-100: Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to add `Pim\Bundle\UserBundle\Context\UserContext`
- AOB-100: Change the constructor of `Pim\Bundle\LocalizationBundle\Controller\FormatController` to add `Pim\Bundle\UserBundle\Context\UserContext`

## Migration

- New data has been indexed in Elasticsearch. Please re-index the products and product models by launching the commands `pim:product:index --all -e prod` and `pim:product-model:index --all -e prod`.

## New jobs
IMPORTANT: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.
- Add the job instance `delete_products_and_product_models`: `bin/console akeneo:batch:create-job "Akeneo Mass Edit Connector" "delete_products_and_product_models" "mass_delete" "delete_products_and_product_models" '{}' "Mass delete products" --env=prod`

# 2.2.0-ALPHA2 (2018-03-07)

## BC breaks

### Constructors

- AOB-2: Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\ConfigureProductGridListner` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\ConfigureProductGridListner` to remove `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
- AOB-2: Add `Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface` to `Pim\Bundle\UserBundle\Repository\UserRepositoryInterface`

## Improve Julia's experience

- PIM-6389: Add attribute value for collections in bulk actions

# 2.2.0-ALPHA1 (2018-02-21)

## Bug fixes

- GITHUB-7641: Fix bug related to product export

## Better manage products with variants

- PIM-7106: Display the 1st variant product created as product model image
- PIM-6334: Add support of product model to the export builder
- PIM-6329: The family variant is now removable from the UI

## BC breaks

### Classes

- PIM-6334: Removal of class `Pim\Component\Connector\Processor\Normalization\ProductModelProcessor`
- PIM-6334: Removal of class `Pim\Component\Connector\Reader\Database\ProductModelReader`
- Remove last argument of method `fromFlatData` in `Pim\Component\Connector\Processor\Denormalization\Product\FindProductToImport`
- Remove class `Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct`
- Remove class `Pim\Bundle\CatalogBundle\EventSubscriber\AddParentAProductSubscriber`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\ConvertProductToVariantProduct`

### Constructors

- PIM-6334: Change the constructor of `Pim\Component\Catalog\Normalizer\Standard\ProductModelNormalizer` to add `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface`
- Change the constructor of `Pim\Component\Connector\Processor\Denormalization\Product` to remove last `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
- Change the constructor of `Pim\Component\Catalog\EntityWithFamilyVariant` to remove the `Pim\Component\Catalog\EntityWithFamily\CreateVariantProduct` dependency.

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
