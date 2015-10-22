# 1.5.x

## Bug fixes

## BC breaks

- Rename service `pimee_product_asset.extension.formatter.property.product_value.product_asset_property` to `pimee_product_asset.datagrid.extension.formatter.property.product_value.product_asset_property`
- Column 'comment' has been added on the `pim_notification_notification` table.
- PropertySetterInterface and PropertyCopierInterface were removed from the PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier\ProductsUpdater and replaced by Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface
- Removed $actionClasses from the PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\ContentDenormalizer constructor
- Moved PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\ValueAction to PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\PropertyAction
- Change constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Normalization\RuleDefinitionProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization\ChannelConfigurationProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
- Change constructor of `PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization\VariationProcessor`. Removed argument `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and add `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`
