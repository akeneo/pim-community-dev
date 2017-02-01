# 1.8

## BC breaks

- Remove methods `setAssets`, `addAsset` and `removeAsset` from `PimEnterprise\Component\Catalog\Model\ProductValueInterface`
- Change the constructor of `PimEnterprise\Component\Catalog\Model\ProductValue` to add `Pim\Component\Catalog\Model\AttributeInterface`, `channel` (string), `locale` (string), `data` (mixed)
- Remove methods `addAsset` and `removeAsset` from `PimEnterprise\Component\Workflow\Model\PublishedProductValue`
- Make method `setAssets` protected for `PimEnterprise\Component\Workflow\Model\PublishedProductValue`
- Change the constructor of `PimEnterprise\Component\Catalog\Model\ProductValue` to add `Pim\Component\Catalog\Model\AttributeInterface`, `channel` (string), `locale` (string), `data` (mixed)
- Remove methods `addAsset` and `removeAsset` from `PimEnterprise\Component\Catalog\Model\ProductValue`
- Make method `setAssets` protected for `PimEnterprise\Component\Catalog\Model\ProductValue`
- Remove doctrine mapping for `PimEnterprise\Component\Catalog\Model\ProductValue`
- Remove doctrine mapping for `PimEnterprise\Component\Workflow\Model\PublishedProductValue`
- Remove class `PimEnterprise\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass`
- Remove method `build` from `PimEnterprise\Bundle\CatalogBundle\PimEnterpriseCatalogBundle`
- Remove method `detachSpecificValues` from `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\DetachProductPostPublishSubscriber`
