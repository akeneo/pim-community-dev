# 3.0

## Technical improvement

- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component 
- PAV3-4: Regroup PAM Classes

## BC breaks

- Move all classes from `PimEnterprise\Bundle\ApiBundle\Normalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\ExternalApi`
- Move all classes from `PimEnterprise\Bundle\EnrichBundle\Controller\Rest` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest`
- Move all classes from `PimEnterprise\Bundle\FilterBundle\Filter\Tag` to `PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter`
- Rename `PimEnterprise\Bundle\EnrichBundle\Controller\Rest\ChannelController` to `PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest\AssetTransformationController`
- Move `PimEnterprise\Bundle\EnrichBundle\Normalizer\AssetNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\InternalApi\AssetNormalizer`
- Move `PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Datagrid\AssetCategoryAccessSubscriber` to `PimEnterprise\Bundle\ProductAssetBundle\Security\AssetCategoryAccessSubscriber`
- Move `PimEnterprise\Bundle\SecurityBundle\Normalizer\Flat\AssetCategoryNormalizer` to `PimEnterprise\Component\ProductAsset\Normalizer\Flat\AssetCategoryNormalizer`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductDraftController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductDraftController`
- Move `PimEnterprise\Bundle\ApiBundle\Controller\ProductProposalController` to `PimEnterprise\Bundle\WorkflowBundle\Controller\Api\ProductProposalController`
- Move `PimEnterprise\Bundle\ApiBundle\Router\ProxyProductRouter` to `PimEnterprise\Bundle\WorkflowBundle\Router\ProxyProductRouter`
- Move `PimEnterprise\Component\Api\Normalizer\ProductNormalizer` to `PimEnterprise\Component\Workflow\Normalizer\ExternalApi\ProductNormalizer`
