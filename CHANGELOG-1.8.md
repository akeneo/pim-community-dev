# 1.8

## Drop MongoDB product storage

- Remove container parameter `pim_catalog_product_storage_driver`
- Remove repository `PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductMassActionRepository`
- Remove repository `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\MongoDBODM\Repository\ProductCascadeRemovalRepository`
- Remove repository `PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository\ProductDraftRepository`
- Remove repository `PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository\PublishedProductRepository`
- Remove class `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDBODM\ProductDraftHydrator`
- Remove class `PimEnterprise\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDBODM\ProductHistoryHydrator`
- Remove event subscriber `PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\MongoDBODM\AssetEventSubscriber`
- Remove event subscriber `PimEnterprise\Bundle\VersioningBundle\EventSubscriber\MongoDBODM\AddProductVersionSubscriber`
- Remove event subscriber `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\MongoDBODM\ExcludeDeletedAttributeSubscriber`
- Remove event subscriber `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\MongoDBODM\RemoveOutdatedProductDraftSubscriber`
- Remove event subscriber `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\SynchronizeProductDraftCategoriesSubscriber`
- Remove model `src/PimEnterprise/Bundle/WorkflowBundle/Resources/config/model/doctrine/ProductDraft.mongodb.yml`
- Remove model `src/PimEnterprise/Bundle/WorkflowBundle/Resources/config/model/doctrine/PublishedProductAssociation.mongodb.yml`
- Remove model `src/PimEnterprise/Bundle/WorkflowBundle/Resources/config/model/doctrine/PublishedProductCompleteness.mongodb.yml`
- Remove model `src/PimEnterprise/Bundle/WorkflowBundle/Resources/config/model/doctrine/PublishedProduct.mongodb.yml`

## BC breaks

- Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher` to add the path of the `logs` directory
- Remove method `link` from `PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface`.
- Change the constructor of `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit` to replace `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` by `Akeneo\Component\StorageUtils\Cache\CacheClearerInterface`.
- Change the constructor `PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilder`. Remove `Doctrine\Common\Persistence\ObjectManager` and add `Pim\Component\Catalog\Factory\ProductValueCollectionFactory` and `Pim\Component\Catalog\Factory\ProductValueFactory`.
- Change the constructor of `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetSaver`. Replace `PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface` by `PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface`.
- Change the constructor of `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetReferenceSaver`. Replace `PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface` by `PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface`.
- Change the constructor of `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetVariationSaver`. Replace `PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface` by `PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface`.
- Remove method `findProducts` of the interface `PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface`
- Remove the inferface `PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface` in favor of `PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface`.
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
- Remove service `pimee_product_asset.denormalizer.pim_assets_collection`
- Change the constructor `PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\DelegatingProductSaver`. Add `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer`.
