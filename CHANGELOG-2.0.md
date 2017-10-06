# 2.0.x

## Better manage products with variants!

- PIM-6343: Classify product models via the edit form

## BC breaks

- Change constructor of `PimEnterprise\Bundle\EnrichBundle\Controller\ProductController` to add `Oro\Bundle\SecurityBundle\SecurityFacade`, an acl and a template 

# 2.0.1 (2017-10-05)

## Bug fixes

- Fix `akeneo:rule:delete` command
- PIM-6843: Fix delete buttons on asset and user pages
- PIM-6853: [IMP] Remove the checkboxes from the attributes grids

## BC breaks

- Remove `$templateUpdater` argument from `PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsUpdater::__construct`

# 2.0.0 (2017-09-28)

# 2.0.0-BETA1 (2017-09-28)

# 2.0.0-ALPHA1 (2017-09-25)

## New API endpoints

- API-71: Get a single product with EE permissions
- API-69: Get a list of products with EE permissions
- API-213: Filter product and product values with EE permissions
- API-224: Try to update partially a product that cannot be viewed through categories
- API-223: Try to update partially a product that can only be viewed through categories
- API-91: Update partially a product that can only be edited through categories with EE permissions
- API-304: Update partially a product that is not classified at all with EE permissions
- API-225: Update partially a product whose code does not exist with EE permissions
- API-72: Update partially a product that is owned through categories with EE permissions
- API-298: Update partially a list of products with EE permissions
- API-70: Create a product with EE permissions
- API-297: Post a media with EE permissions
- API-74: Delete a product with EE permissions
- API-229: Get a draft
- API-228: Submit a draft for approval
- API-240: Know the status of a working copy
- API-260: Get a filtered list of published products
- API-259: Get a list of published products

## EE permissions on the imports, exports and products deletion
- API-349: Apply permissions on published products export
- API-247: Apply permissions on products export
- API-303: Apply permissions on mass product deletion

## Remove MongoDB product storage

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

## Remove variant groups

- Remove service `pimee_enrich.step.add_to_variant_group_with_rules.perform`
- Remove service `pim_enrich.job.add_to_variant_group`
- Remove service `pimee_catalog_rule.view_element.common.variant_attribute_from_smart`

- Remove parameter `pimee_enrich.job_name.add_to_variant_group_with_rules`

## BC breaks

- Change the constructor of `PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber\RuleExecutionSubscriber` to replace `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` by `Symfony\Component\Security\Core\User\ChainUserProvider`.
- Change the constructor of `PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductMassActionRepository` to add `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` and CategoryAccess parameter class.
- Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Proposal\ContextConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher` to add the path of the `logs` directory
- Remove method `link` from `PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface`.
- Change the constructor of `PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit` to replace `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` by `Akeneo\Component\StorageUtils\Cache\CacheClearerInterface`.
- Change the constructor `PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilder`. Remove `Doctrine\Common\Persistence\ObjectManager` and add `Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface` and `Pim\Component\Catalog\Factory\ValueFactory`.
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
- Change the constructor `PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\DelegatingProductSaver` to add `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer`.
- Change the constructor `PimEnterprise\Bundle\WorkflowBundle\Controller\Rest\ProductDraftController` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
- Remove class `Pim\Bundle\InstallerBundle\Persister\YamlPersister`
- Remove service `pim_installer.yaml_persister`
- Change the constructor of `PimEnterprise\Bundle\EnrichBundle\Controller\ProductController` to add `Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- PIM-6228: remove escape parameter from csv imports.
- Change the constructor of `PimEnterprise\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer` to add `Pim\Component\Catalog\Repository\ChannelRepositoryInterface` and `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`.
- PIM-6815: Remove `indexAction` from `PimEnterprise\Bundle\ProductAssetBundle\Controller\ProductAssetController`
- PIM-6815: Remove `src/PimEnterprise/Bundle/ProductAssetBundle/Resources/views/ProductAsset/index.html.twig`
- Change the constructor of `PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher` to add `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` and remove `rootDirectory`(string), `environment` (string), `logDir` (string).


## Type hint

- Add type hint `Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface` to the return of the function `select` of `Akeneo\Bundle\RuleEngineBundle\Engine\SelectorInterface`
- Add type hint `array` to the return of the function `dryRunAll` of `Akeneo\Bundle\RuleEngineBundle\Runner\BulkDryRunnerInterface`
- Add type hint `array` to the return of the function `runAll` of `Akeneo\Bundle\RuleEngineBundle\Runner\BulkRunnerInterface`
- Add type hint `Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface` to the return of the function `dryRun` of `Akeneo\Bundle\RuleEngineBundle\Runner\DryRunnerInterface`
- Add type hint `bool` to the return of the function `supports` of `Akeneo\Bundle\RuleEngineBundle\Runner\RunnerInterface`
