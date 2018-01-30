# 2.2.x

## Improve Julia's experience

- PIM-6367: Apply rules on products models values

## BC breaks

### Constructors

- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver` to add `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\SetterActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- PIM-6367: Change the constructor of `PimEnterprise\Component\CatalogRule\ActionApplier\AdderActionApplier` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`

### Services and parameters

- PIM-6367: Remove argument `pim_catalog.resolver.attribute_values_with_permissions` from service `pimee_workflow.builder.published_product`
