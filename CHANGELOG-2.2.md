# 2.2.10 (2018-06-22)

# 2.2.9 (2018-06-14)

- PIM-7384: Fix Memory leak on Quick export

# 2.2.7 (2018-05-31)

# 2.2.6 (2018-05-24)

# 2.2.5 (2018-05-16)

# 2.2.4 (2018-04-26)

## Bug fixes

- PIM-7314: handle all attribute case for product proposal migration
- PIM-7310: Fix completeness filter in rules to have the operators '=', '!=', '>', '<'

# 2.2.3 (2018-04-12)

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

- PIM-7187: Apply rules after mass edit on product models
- PIM-7090: Add completeness filter on product model export builder
- PIM-7091: Build exports for products models according to their codes
- PIM-7143: Be able to delete products and product models in mass using a backend job
- PIM-6803: Message when delete a family with family variant.

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

# 2.2.0-ALPHA2 (2018-03-07)

## Proposal improvements

- AOB-2: Add filters on the proposals screen

## BC breaks

### Constructors

- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassApproveActionHandler` to add `Akeneo\Bundle\ElasticsearchBundle\Cursor\CursorFactoryInterface` as new argument.
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassRefuseActionHandler` to add `Akeneo\Bundle\ElasticsearchBundle\Cursor\CursorFactoryInterface` as new argument.
- AOB-2: Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Security\Elasticsearch\ProductQueryBuilderFactory` to add `$accessLevel` as new argument.
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` to remove `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal`
         to remove `PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface`,
                   `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Symfony\Component\HttpFoundation\RequestStack`,`PimEnterprise\Bundle\WorkflowBundle\Provider\ProductDraftGrantedAttributeProvider`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\ORM\ProductDraftHydrator` to add `PimEnterprise\Bundle\WorkflowBundle\Datagrid\Normalizer\ProductProposalNormalizer`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProposalGridListener` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassApproveActionHandler` to remove `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface`
- AOB-2: Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler\MassRefuseActionHandler` to remove `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface`
- AOB-2: `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` now implements `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface`
- AOB-2: `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` now implements `Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface`
- AOB-2: `PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\ProductDraftRepository` now implements `Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface`
- AOB-2: `PimEnterprise\Component\Workflow\Model\ProductDraftInterface` now implements `Pim\Component\Catalog\Model\EntityWithValuesInterface`
- AOB-2: `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator` now implements `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\ConfigureProductGridListner` to add `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- AOB-2: Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\ConfigureProductGridListner` to remove `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
- AOB-2: Add `Akeneo\Component\StorageUtils\Repository\CountableRepositoryInterface` to `Pim\Bundle\UserBundle\Repository\UserRepositoryInterface`

## New jobs
Be sure to run the following command `bin/console pim:installer:grant-backend-processes-accesses --env=prod` to add missing job profile accesses.

## Improve Julia's experience

- PIM-6389: Add attribute value for collections in bulk actions

# 2.2.0-ALPHA1 (2018-02-21)

## Improve Julia's experience

- PIM-7125: As Peter, I would like to use rules to unclassify products from a tree
- PIM-7186: Don't apply a rule if its action field is an attribute not present in the family

## Bug fixes

- GITHUB-7641: Fix bug related to product export

## Better manage products with variants

- PIM-7165: Execute rules after a product model import
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

- PIM-7125: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\RemoverActionApplier` to add `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- PIM-7125: Change the constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Twig\RuleExtension` to add `Symfony\Component\Translation\TranslatorInterface`
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

## Better manage products with variants

- PIM-6367: Apply rules on products models values (this also fixes PIM-7235)
- PIM-7166: Display on product model edit form that an attribute can be updated by a rule

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

- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver` to add `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\SetterActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\AdderActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\RemoverActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\CopierActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `Pim\Component\Connector\Processor\Normalization\ProductProcessor` to add `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- PIM-6367: Change the constructor of `Pim\Component\Connector\Writer\Database\ProductModelDescendantsWriter` to remove `Pim\Component\Catalog\Builder\ProductBuilderInterface`
    and to add `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- PIM-6367: Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\ProductDatasource` to add `Pim\Bundle\DataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber`
- PIM-6367: Change the constructor of `Pim\Component\Catalog\Builder` to remove `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface`
- Change the constructor of `Pim\Bundle\CatalogBundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber` to add `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` and `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`

### Services and parameters

- PIM-6367: Remove argument `pim_catalog.resolver.attribute_values_with_permissions` from service `pimee_workflow.builder.published_product`
- PIM-6367: Rename service `pimee_enrich.query.product_and_product_model_query_builder_factory_with_permissions`
    into `pimee_catalog.query.product_and_product_model_query_builder_factory_with_permissions`
- PIM-6367: Rename service `pimee_enrich.query.product_and_product_model_query_builder_factory.with_permissions_and_product_and_product_model_cursor`
    into `pimee_catalog.query.product_and_product_model_query_builder_factory.with_permissions_and_product_and_product_model_cursor`
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
