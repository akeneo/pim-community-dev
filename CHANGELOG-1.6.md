# 1.6.x

## Functional improvements

- PIM-5096: Adds XLSX quick export to the product grid and published product grid
- PIM-5356: Add the following XLSX job export: assets, asset categories and asset variations
- PIM-5640: Locale and attribute group permissions are now applied on quick export content
- PIM-5357: Add the following XLSX job import: assets and asset categories

## Technical improvements

- PIM-5589: introduce a channels, attribute groups, group types, currencies, locale accesses, asset category accesses, product category accesses, attribute group accesses and job profile accesses import using the new import system introduced in v1.4
- PIM-5645: introduces the new Akeneo XLSX Connector
- TIP-342: be able to launch mass edit processes without having to previously store a JobConfiguration and only rely on dynamic configuration
- PIM-5577: The completeness is now calculated every time a product is saved, ie during mass edit, rule execution, product import and on edit/save of variant groups.

## BC breaks

- Remove `PimEnterprise\Bundle\CatalogBundle\Manager\ProductCategoryManager`
- In `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository`, remove method `getGrantedCategoryIdsFromQB` and replace it by `getGrantedChildrenIds`
- Change constructor of `PimEnterprise\Bundle\UserBundle\Form\Type\UserType`. Remove the last parameter `%pimee_product_asset.model.category.class%` and replace it by `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface`
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Import\ImportProposalsSubscriber`. Replace deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` by `Pim\Bundle\NotificationBundle\NotifierInterface` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\AbstractProposalStateNotificationSubscriber`. Replace deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` by `Pim\Bundle\NotificationBundle\NotifierInterface` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\SendForApprovalSubscriber`. Replace deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` by `Pim\Bundle\NotificationBundle\NotifierInterface` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`.
- Change constructor of `PimEnterprise\Bundle\ProductAssetBundle\MassUpload\MassUploadTasklet`. Remove deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager`.
- Change constructor of `PimEnterprise\Bundle\CatalogRuleBundle\Controller\RuleController`. Add `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface`, `PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository` and `PimEnterprise\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter`.
- Change constructor of `PimEnterprise\Component\ProductAsset\VariationFileGenerator`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`.
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\DetachProductPostPublishSubscriber`. Replace `Pim\Bundle\CatalogBundle\Manager\ProductManager` by `Doctrine\Common\Persistence\ObjectManager`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface`.
- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- Installer fixtures now support csv format for attribute groups setup and not anymore the yml format
- Installer fixtures now support csv format for group types setup and not anymore the yml format
- Installer fixtures now support csv format for currencies setup and not anymore the yml format
- Installer fixtures now support csv format for locale accesses setup and not anymore the yml format
- Installer fixtures now support csv format for asset category accesses setup and not anymore the yml format
- Installer fixtures now support csv format for product category accesses setup and not anymore the yml format
- Installer fixtures now support csv format for attribute group accesses setup and not anymore the yml format
- Installer fixtures now support csv format for job profile accesses setup and not anymore the yml format
- Installer fixtures now support csv format for asset categories setup and not anymore the yml format
- Add `Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator` as last parameter of
    `Pim\Component\Connector\ArrayConverter\Flat\AssociationTypeStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeGroupStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeOptionStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\CategoryStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\ChannelStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\FamilyStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\GroupStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter,`
    `Pim\Component\Connector\ArrayConverter\Flat\VariantGroupStandardConverter` and
    `Pim\Component\Connector\ArrayConverter\Structured\AttributeOptionStandardConverter`
- Remove deprecated argument $propertyCopier from constructor of `Pim\Component\Catalog\Updater\ProductUpdater` and allow to inject supported fields
- AttributeGroupAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository` $repository, `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` $saver, $attGroupAccessClass as constructor arguments
- LocaleAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository` $repository, BulkSaverInterface $saver, $localeClass as constructor arguments
- JobProfileAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository` $repository, `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` $saver, $localeClass as constructor arguments
- CategoryAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository` $repository, BulkSaverInterface $saver, $categoryClass as constructor arguments
- Move `PimEnterprise\Bundle\SecurityBundle\Model\AccessInterface` to `PimEnterprise\Component\Security\Model\AccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\AttributeGroupAccessInterface` to `PimEnterprise\Component\Security\Model\AttributeGroupAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface` to `PimEnterprise\Component\Security\Model\CategoryAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\JobProfileAccessInterface` to `PimEnterprise\Component\Security\Model\JobProfileAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\LocaleAccessInterface` to `PimEnterprise\Component\Security\Model\LocaleAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AccessRepositoryInterface` to `PimEnterprise\Component\Security\Repository\AccessRepositoryInterface`
- `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository` now implements `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository\AssetCategoryRepository` now implements `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository` now implements `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Controller\PublishedProductController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Model` to `PimEnterprise\Component\Workflow\Model`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface` to `PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Event` to `PimEnterprise\Component\Workflow\Event`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Exception` to `PimEnterprise\Component\Workflow\Exception`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Factory` to `PimEnterprise\Component\Workflow\Factory`.
- Remove class `PimEnterprise\Bundle\WorkflowBundle\Factory\UploadedFileFactory`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Normalizer` to `PimEnterprise\Component\Workflow\Normalizer`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Applier` to `PimEnterprise\Component\Workflow\Applier`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Repository` to `PimEnterprise\Component\Workflow\Repository`.
- Move `PimEnterprise\Bundle\WorkflowBundle\PimEnterprise\Helper\SortProductValuesHelper` to `PimEnterprise\Bundle\WorkflowBundle\Twig\SortProductValuesHelper`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Publisher` to `PimEnterprise\Component\Workflow\Publisher`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Connector\Tasklet` to `PimEnterprise\Component\Workflow\Connector\Tasklet`.
- Move `PimEnterprise\Bundle\CatalogBundle\Model` to `PimEnterprise\Component\Catalog\Model`.
- Move `PimEnterprise\Bundle\SecurityBundle\Attributes` to `PimEnterprise\Component\Security\Attributes`.
- Remove parameter `pimee_workflow.publisher.product_media.class` because class was removed in 1.4.
- Rename and move `PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\FilePublisher` to `PimEnterprise\Component\Workflow\Publisher\Product\FileInfoPublisher`.
- Move namespace `Pim\Bundle\TransformBundle\Normalizer\Flat` to `PimEnterprise\Bundle\SecurityBundle\Normalizer\Flat`
- Remove class `PimEnterprise\Bundle\BaseConnectorBundle\Processor\AbstractAccessProcessor`
- Remove class `PimEnterprise\Bundle\BaseConnectorBundle\Processor\AssetCategoryAccessProcessor`
- Remove class `PimEnterprise\Bundle\BaseConnectorBundle\Processor\AttributeGroupAccessProcessor`
- Remove class `PimEnterprise\Bundle\BaseConnectorBundle\Processor\CategoryAccessProcessor`
- Remove class `PimEnterprise\Bundle\BaseConnectorBundle\Processor\JobProfileAccessProcessor`
- Remove class `PimEnterprise\Bundle\BaseConnectorBundle\Processor\LocaleAccessProcessor`
- Remove class `Pim\Bundle\ConnectorBundle\JobLauncher\SimpleJobLauncher`  which overrides `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` we now always use `@akeneo_batch.launcher.simple_job_launcher` and not anymore `@pim_connector.launcher.simple_job_launcher`
- Remove parameter `Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface` from constructors of 
    `Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Family\SetAttributeRequirements`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductToVariantGroupProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductValueProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\UpdateProductValueProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`
    `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredFamilyReader`
    `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductReader`
    `PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductValueWithPermissionProcessor`
    `PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor`
    `PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\UpdateProductValueWithPermissionProcessor`
- Remove class `Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface`
- Remove class `Pim\Component\Connector\Factory\JobConfigurationFactory`
- Remove class `Pim\Component\Connector\Model\JobConfiguration`
- Remove class `Pim\Component\Connector\Model\JobConfigurationInterface`
- Removed the `recalculate` and `schedule` option from the `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver` and `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver`
- Remove methods `setConfig` and `getConfig` from `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface`
- Change the method `launch` of `Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface`, `$configuration` is now an array and not a string anymore
- Remove deprecated method `setName` from `Akeneo\Component\Batch\Job\Job`
- Remove deprecated classes `Pim\Bundle\BaseConnectorBundle\Step\ValidatorStep` and `Pim\Bundle\BaseConnectorBundle\Validator\Step\CharsetValidator`
- Remove methods `setEventDispatcher` and `setJobRepository` from `Akeneo\Component\Batch\Job\Job`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Reader\DummyReader`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Validator\Import\SkipImportValidator`
- Remove deprecated `PimEnterprise/Component/CatalogRule/Validator/ExistingFieldValidator`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/Model/ProductSetValueActionInterface`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/Model/ProductSetValueAction`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/Model/ProductCopyValueActionInterface`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/Model/ProductCopyValueAction`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/Denormalizer/ProductRule/SetValueActionDenormalizer`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/Denormalizer/ProductRule/CopyValueActionDenormalizer`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/ActionApplier/SetterValueActionApplier`
- Remove deprecated `PimEnterprise/Bundle/CatalogRuleBundle/ActionApplier/CopierValueActionApplier`
- Change constructor of `PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager`
    remove `Doctrine\Common\Persistence\ObjectManager`
    remove `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`
    remove `Symfony\Component\EventDispatcher\EventDispatcherInterface`
    remove parameter `$categoryClass`
    That class does not extends `Pim\Bundle\CatalogBundle\Manager\CategoryManager` anymore because it has been removed en CE.
- Change constructor of `PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`
    add  `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
    remove parameter `$categoryClass`
- Remove deprecated `PimEnterprise\Component\CatalogRule\Connector\Writer\YamlFile\RuleDefinitionWriter`
- Remove argument array $configuration from the method `execute()` of classes
    `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\AbstractProductPublisherTasklet`,
    `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\PublishProductTasklet`,
    `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet\UnpublishProductTasklet`,
    `PimEnterprise\Bundle\ProductAssetBundle\MassUpload\MassUploadTasklet`
	`PimEnterprise\Component\CatalogRule\Connector\Tasklet\ImpactedProductCountTasklet`
	`PimEnterprise\Component\Workflow\Connector\Tasklet\ApproveTasklet`
	`PimEnterprise\Component\Workflow\Connector\Tasklet\RefuseTasklet` we can access to the JobParameters from the StepExecution
