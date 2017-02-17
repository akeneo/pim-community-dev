# 1.8

## BC breaks

- Remove services `pimee_versioning.denormalizer.product`, `pimee_versioning.denormalizer.family`, `pimee_versioning.denormalizer.category`, `pimee_versioning.denormalizer.group`
    `pimee_versioning.denormalizer.association`, `pimee_versioning.denormalizer.product_value`, `pimee_versioning.denormalizer.base_value`, `pimee_versioning.denormalizer.attribute_option`
    `pimee_versioning.denormalizer.attribute_options`, `pimee_versioning.denormalizer.prices`, `pimee_versioning.denormalizer.metric`, `pimee_versioning.denormalizer.datetime`
    `pimee_versioning.denormalizer.file`, `pimee_versioning.denormalizer.reference_data` and `pimee_versioning.denormalizer.reference_data_collection`
- Change the constructor of `PimEnterprise\Bundle\VersioningBundle\Reverter\ProductReverter` replace `Symfony\Component\Serializer\SerializerInterface` by `Pim\Component\Catalog\Updater\ProductUpdater` and add `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product`
- Change the constructor of `PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager` to add `Akeneo\Component\StorageUtils\Saver\SaverInterface`
- Change the constructor of `PimEnterprise\Component\Workflow\Publisher\ProductPublisher` to add `Symfony\Component\Serializer\SerializerInterface` and `Pim\Component\Catalog\Updater\ObjectUpdaterInterface`
- Remove class `PimEnterprise\Component\Workflow\Publisher\AttributeOptionPublisher`
- Remove class `PimEnterprise\Component\Workflow\Publisher\Product\ValuePublisher`
- Remove class `PimEnterprise\Component\Workflow\Publisher\Product\FileInfoPublisher`
- Remove class `PimEnterprise\Component\Workflow\Publisher\Product\MetricPublisher`
- Remove class `PimEnterprise\Component\Workflow\Model\PublishedProductMetric`
- Remove class `PimEnterprise\Component\Workflow\Model\PublishedProductMetricInterface`
- Remove class `PimEnterprise\Component\Workflow\Publisher\Product\PricePublisher`
- Remove class `PimEnterprise\Component\Workflow\Model\PublishedProductPrice`
- Remove class `PimEnterprise\Component\Workflow\Model\PublishedProductPriceInterface`
- Remove service `pimee_workflow.publisher.product_value` and parameter `pimee_workflow.publisher.product_value.class`
- Remove service `pimee_workflow.publisher.product_file` and parameter `pimee_workflow.publisher.product_file.class`
- Remove service `pimee_workflow.publisher.product_metric` and parameter `pimee_workflow.publisher.product_metric.class`
- Remove service `pimee_workflow.publisher.product_price` and parameter `pimee_workflow.publisher.product_price.class`
- Remove service `pimee_workflow.publisher.attribute_option` and parameter `pimee_workflow.publisher.attribute_option.class`
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
