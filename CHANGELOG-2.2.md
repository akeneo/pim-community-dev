# 2.2.x

## Improve Julia's experience

- PIM-6367: Apply rules on products models values
- PIM-7166: Display on product model edit form that an attribute can be updated by a rule

## BC breaks

### Constructors

- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver` to add `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\SetterActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\AdderActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\RemoverActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\CopierActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`

### Services and parameters

- PIM-6367: Remove argument `pim_catalog.resolver.attribute_values_with_permissions` from service `pimee_workflow.builder.published_product`
- PIM-6367: Rename service `pimee_enrich.query.product_and_product_model_query_builder_factory_with_permissions`
    into `pimee_catalog.query.product_and_product_model_query_builder_factory_with_permissions`
- PIM-6367: Rename service `pimee_enrich.query.product_and_product_model_query_builder_factory.with_permissions_and_product_and_product_model_cursor`
    into `pimee_catalog.query.product_and_product_model_query_builder_factory.with_permissions_and_product_and_product_model_cursor`
