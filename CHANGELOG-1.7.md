# 1.7.x

## Bug fixes

## Functional improvements

## Technical improvements

## BC breaks

- Move `Akeneo\Bundle\RuleEngineBundle\Normalizer\RuleNormalizer` to `Akeneo\Bundle\RuleEngineBundle\Normalizer\Standard\RuleNormalizer`
- Move `PimEnterprise\Component\Catalog\Normalizer\Structured\AttributeNormalizer` to `PimEnterprise\Component\Catalog\Normalizer\Standard\AttributeNormalizer`
- Move `PimEnterprise\Component\ProductAsset\Normalizer\Structured\AssetNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Standard\AssetNormalizer`
- Move `PimEnterprise\Component\ProductAsset\Normalizer\Structured\ChannelConfigurationNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Standard\ChannelConfigurationNormalizer`
- Move `PimEnterprise\Component\ProductAsset\Normalizer\Structured\VariationNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Standard\VariationNormalizer`
- Rename service `pimee_serializer.normalizer.structured.attribute` to `pimee_catalog.normalizer.standard.attribute`
- Rename service `pim_product_asset.normalizer.structured.asset` to `pimee_product_asset.normalizer.standard.asset`
- Rename service `pim_product_asset.normalizer.structured.variation` to `pimee_product_asset.normalizer.standard.variation`
- Rename service `pim_product_asset.normalizer.structured.channel_configuration` to `pimee_product_asset.normalizer.standard.channel_configuration`
- Rename service `pim_product_asset.normalizer.flat.asset` to `pimee_product_asset.normalizer.flat.asset`
- Change the constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Twig\RuleExtension` to add `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository`
- Change the constructor of `PimEnterprise\Bundle\WorkflowBundle\Presenter\FilePresenter` to add `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface`
