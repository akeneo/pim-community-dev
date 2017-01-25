# 1.7.x

## Bug fixes

## Functional improvements

## Technical improvements

- Update to Symfony 2.7.23
- Update spec to be independent from actual developer timezone

## BC breaks

- Add `applyCategoriesFilter` to `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface` 
- Replace `Pim\Component\Catalog\Query\Filter\FieldFilterInterface` in `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\AssetCategoryAccessSubscriber` by `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface`
- Remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter` of `PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter\ProductAssetFilterUtility`
- Remove WebServiceBundle
- Remove `wsse_secured` firewall in security.yml
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
- Update classes and services to use the interface `Pim\Component\User\Model\GroupInterface`in place of `Oro\Bundle\UserBundle\Entity\Group`
