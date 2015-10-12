# 1.5.x

## Bug fixes

## BC breaks

- Rename service `pimee_product_asset.extension.formatter.property.product_value.product_asset_property` to `pimee_product_asset.datagrid.extension.formatter.property.product_value.product_asset_property`
- Column 'comment' has been added on the `pim_notification_notification` table.
- PropertySetterInterface and PropertyCopierInterface were removed from the PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater and replaced by Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface
