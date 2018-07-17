# 1.6.23 (2018-04-03)

# 1.6.22 (2018-02-26)

# 1.6.21 (2018-01-11)

## Bug fixes

- PIM-7076: Fix sort order in product view form

# 1.6.20 (2017-10-25)

# 1.6.19 (2017-09-05)

- PIM-6770: Fix assets completeness calculation

# 1.6.18 (2017-07-17)

# 1.6.17 (2017-06-30)

## Bug fixes

- PIM-6433: Fix product import reference data multi with duplicate code options
- PIM-6438: Add a link in the notification report of "calculate affected products" rules mass action, that redirects to job process
- PIM-6475: Generate asset variations, even if reference file has corrupted metadata

# 1.6.16 (2017-05-24)

# 1.6.15 (2017-05-18)

# 1.6.14 (2017-04-21)

## Bug fixes

- PIM-6325: Fix thumbnails display for assets in product PDF

# 1.6.13 (2017-03-29)

## Bug fixes

- PIM-6268: Fix an issue where one cannot scroll locales on asset page when too many locales are activated
- PIM-6267: Fix an error when you add assets to a variant group, products were unticked
- PIM-6269: Fix the display of rules containing array of dates in their conditions (IN, BETWEEN...)

# 1.6.12 (2017-02-28)

# 1.6.11 (2017-02-14)

# 1.6.10 (2017-02-02)

## Bug fixes

- PIM-6079: Add 'All' permissions for new attribute groups

# 1.6.9 (2017-01-17)

# 1.6.8 (2017-01-05)

# 1.6.7 (2016-12-20)

## Bug fixes

- PIM-6035: Fix an issue with locale switcher when a user does not have access to first locale of the list

# 1.6.6 (2016-12-08)

## Bug fixes

- PIM-6016: Add a missing validation on asset attributes import

# 1.6.5 (2016-11-25)

## Bug fixes

- PIM-6011: Fix an error 500 on asset's view screen caused by a typo

## Developer eXperience

- Ease the override of product asset by using model parameter in the product-asset grid

# 1.6.4 (2016-10-20)

## Bug fixes

- PIM-5981: Fix wording issue on asset bulk action
- PIM-5988: Fix Product Assets controller & repository to correctly add assets by their code

# 1.6.3 (2016-09-22)

## Bug fixes

- PIM-5821: Apply rights on locale specific values during quick export
- PIM-5978: Fix missing currencies

# 1.6.2 (2016-09-02)

# 1.6.1 (2016-09-01)

- TIP-574: Fix a regression on completeness calculation affecting the duration of a product save.

## Bug fixes

- PIM-5782: Add right filtering on locales for export builder
- PIM-5949: Fix scope and locale switches on published product edit form
- PIM-5961: Remove translations of BaseConnectorBundle (synchronization issue with Nelson)
- PIM-5959: Change wording of XLSX to Excel on mass actions

# 1.6.O (2016-08-30)

## Bug fixes

# 1.6.O-RC1 (2016-08-29)

## Bug fixes

# 1.6.0-ALPHA2 (2016-08-23)

## Bug fixes

- PIM-5915: Fix the import of localizable and scopable variant group attributes
- PIM-5929: Fix the validation issue indicator appearance on form tabs

# 1.6.0-ALPHA1 (2016-08-01)

## Bug fixes

- PIM-5854: The family code is not displayed at all in the product grid when no family labels
- PIM-5888: Fix an outline glitch on some buttons
- PIM-5869: Allow any codes to be used for attributes
- PIM-5434: When no access to any category, I can't edit my profile
- PIM-5936: Fix the published indicator display in the history panel

## Functional improvements

### Improve Rules Engine

- PIM-5665: As Peter, I would like to remove products from categories using rules (mass edit, add "remove" action in rule engine)
- PIM-5742: Improve the products save for mass operations, I would like to have the completeness and rules calculated directly
- PIM-5577: As Julia, when I save several products, I would like to have the completeness and rules calculated directly
- PIM-5757: As Peter, I would like to make bulk actions on the rules (dd delete bulk action on the rules datagrid)
- PIM-5813: As Peter, I would like to launch manually 1 or all rules from the UI (add the possibility to launch the rules from the UI)
- PIM-5860: As Peter, I would like to launch manually a selection of rules (add the execute mass action on rules)
- PIM-5364: As Mary, when I mass edit my products, I would like to have the rules executed
- PIM-5582: As Peter, I would like to define that an attribute cannot be updated by screen(rRead only attribute)
- PIM-5533: As Peter, I would like to refine the rules conditions with NOT EMPTY conditions
- PIM-5365: As Peter, I would like to define a rule with opposite conditions (NOT IN, !=) to the action
- PIM-5363: As Peter, when I make a product import, I would like to have the rules calculated
- PIM-5455: As Peter, when I define a rule, I would like to know the number of products impacted by the rule
- PIM-5892: Change warning message when executing all rules from the UI

### Improve Asset Manager

- PIM-5488: As Pamela, I would like to move the assets in the trees (add a bulk action on the product assets grid, to move them in categories)
- PIM-5489: As Pamela, I would like to mass add tags for a selection of assets (add a bulk action on the product assets grid, to add tags on assets)
- PIM-5490: As Pamela, I would like to mass delete assets (add delete bulk action on the product assets grid)
- PIM-5530: Change the label and the position of the button "Schedule" in "Upload assets" screen
- PIM-5778 [Cookbook] How to automate assets mass import using the existing `pim:product-asset:mass-upload`

### Support Excel Files

- PIM-5645: As Peter, I would like to have a dedicated connector for Excel files
- PIM-5435: As Mary, I would like to import and export products with Excel files
- PIM-5100: As Peter, I would like to export variant groups and groups in Excel files
- PIM-5095: As Peter, I would like to import variant groups and groups with Excel files
- PIM-5099: As Peter, I would like to export the catalog structure in Excel files
- PIM-5097: As Peter, I would like to import the catalog structure in Excel files
- PIM-5098: As Mary, I would like to export products and published products in Excel files
- PIM-5096: As Mary, I would like to quick export products and published products in Excel files
- PIM-5094: As Mary, I would like to download the invalid data in Excel files
- PIM-5093: As Mary, I would like to import products and products proposal with Excel files
- PIM-5692: As Mary, I would like to import localized Excel files
- PIM-5641: As Mary, I would like to define a limit number of lines for the Excel files in order to manage them easily in Excel
- PIM-5612: As Peter, I would like to know the Excel versions supported by our Excel connector
- PIM-5357: Add the following XLSX job import: assets and asset categories
- PIM-5356: Add the following XLSX job export: assets, asset categories and asset variations
- PIM-5099: The catalog structure can now be exported in XLSX format (families, attributes, attribute options, association types and categories)
- PIM-5097: The catalog structure can now be imported in XLSX format (families, attributes, attribute options, association types and categories)

### Export Builder

- PIM-5833: As Peter, I would like to choose for which categories the products are exported - Back End
- PIM-5653: As Filips, I would like to export products and configure the filters with Product QUery Builder
- PIM-5112: As Peter, I would like to export only the products updated since the last export
- PIM-5657: As Peter, I would like to configure my product export profiles in few tabs
- PIM-5809: [Spike] Export builder (ajaxification)
- PIM-5145: As Peter, I would like to choose for which locales the products are exported
- PIM-5426: As Peter, I would like to filter on the completeness to export products
- PIM-5427: As Peter, I would like to filter on the family to export products
- PIM-5431: As Peter, I would like to export the products updated since a defined date
- PIM-5428: As Peter, I would like to filter on the status to export products
- PIM-5421: As Peter, I would like to choose for which categories the products are exported - Front End Revamp
- PIM-5633: As Peter, I would like to filter on a list of product identifiers to export products
- PIM-5110: As Peter, I would like to choose if the products images and files have to be exported
- PIM-5432: As Peter, I would like to export the products updated since the last n days
- PIM-5109: As Peter, I would like to choose the products attributes to export
- PIM-5634: As Peter, I would like to filter on a list attribute to export products
- PIM-5635: As Peter, I would like to filter on all attributes types to export products

### User Productivity

- PIM-5602: As Mary, I would like to move products from a category to another category (mass edit)
- PIM-5604: As Mary, I would like to remove products from a category (mass edit)
- PIM-5592: As Mary, when I enrich a product and come back to the grid, I would like to keep the page number of the grid
- PIM-5743: As Peter, I would like to export / import the attributes with all their properties
- PIM-5600: As Mary, I would like to quick export only the columns, locale and channel of the grid
- PIM-5664: As Peter, I would like to purge my job execution history
- PIM-5761: Remove the useless color property in the channel edit form
- PIM-5681: As Peter, I would like to purge my entities history, introduce a new command to purge entity versions stored in the PIM (see pim:versioning:purge command)
- PIM-5647: As Mary, when I load the PEF, I don't want to have a blank screen
- PIM-5593: As Julia, when I add an association to a product, I would like to keep my context in the grid
- PIM-5624: As Julia, when I come back to a grid, I would like to keep the page number of the grid
- PIM-5657: It is now possible to add custom tabs within the job profile and edit pages
- PIM-5700: Move the channel our of the association grid filter
- PIM-5594: As Julia, when I edit in sequential some products, I would like to keep the completeness panel displayed
- PIM-5781: Add new data on the "system information" screen (data volumetry, information about the operating system)
- PIM-5736: As Mary, I would like to have different bulk actions to facilitate the use
- PIM-5640: As Mary, I would like that my permissions are taken into account if I quick export products (locale and attribute group permissions are now applied on quick export content)

## Technical improvements

- PIM-5589: Introduce a channels, attribute groups, group types, locales and currencies import using the new import system introduced in v1.4
- PIM-5589: Introduce a SimpleFactoryInterface to create simple entities
- PIM-5589: Introduce a channels, attribute groups, group types, currencies, locale accesses, asset category accesses, product category accesses, attribute group accesses and job profile accesses import using the new import system introduced in v1.4
- PIM-5594: Panel state is now stored in the session storage
- PIM-5645: Bath jobs configuration files can now also be loaded when contained in a folder named 'batch_jobs'. Introduces the new Akeneo Product XLSX Connector
- TIP-342: be able to launch mass edit processes without having to previously store a JobConfiguration and only rely on dynamic configuration
- PIM-5577: The completeness is now calculated every time a product is saved, ie during mass edit, product import and on edit/save of variant groups.
- Call validation in the controller when adding/removing attributes to the family.
- Simplify installation process and the loading of catalogs in Behat by using the import system and `akeneo:batch:job` commands.
- PIM-5653: When using the Product Query Builder, it is now possible to filter on completeness without specifying a locale. Products with a matching completeness for at least one of the locales of the scope will be selected.
- PIM-5653: Introduce a new storage-agnostic Product Reader using the PQB
- PIM-5742: Schedule completeness for ORM is now performed directly through SQL
- Integrates the AkeneoMeasureBundle in our main repository
- TIP-245: Add datetime filters in the Product Query Builder, allowing to select products on "created at" and "updated at" fields.
- PIM-5657: Introduce a `JobTemplateProvider` that holds the job template codes to use for creating, showing, editing job profiles. The provider uses configuration files in order to retrieve overridden templates for specific job names
- TIP-458: Move the Converters from Processors to Readers. Now, all the readers return a standard format as output, and all the processors get a standard format as input.
- TIP-459: Standardize the denormalization Processors, to use SimpleProcessor in most of the cases.
- TIP-255: Allow to select PQB filter on supported operator, to add new operators easily on existing fields/attribute types
- PIM-5781: Introduce a new command to get system information from the command line
- TIP-535: Remove the flush option from SaverInterface, BulkSaverInterface, RemoverInterface, BulkRemoverInterface, thank you @iulyanp!

## BC breaks

- Rename `PimEnterprise\Component\ProductAsset\Connector\Reader\Doctrine\AssetCategoryReader` to `PimEnterprise\Component\ProductAsset\Connector\Reader\Database\AssetCategoryReader`.
- Change constructor of `PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization\ChannelConfigurationProcessor`. Remove `Symfony\Component\Serializer\SerializerInterface` and `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`.
- Change constructor of `PimEnterprise\Component\CatalogRule\Connector\Processor\Normalization\RuleDefinitionProcessor`. Remove `Symfony\Component\Serializer\SerializerInterface` and `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`.
- Rename `PimEnterprise\Component\Workflow\Connector\ArrayConverter\FlatToStandard\ProductDraft` to `PimEnterprise\Component\Workflow\Connector\ArrayConverter\FlatToStandard\ProductDraftChanges`
- Rename `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard\Tag` to `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard\Tags`
- Change constructor of `PimEnterprise\Component\Workflow\Connector\Processor\Denormalization\ProductDraftProcessor`. Remove `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Change constructor of `PimEnterprise\Component\Workflow\Applier\ProductDraftApplier`. Add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`.
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
- Change constructor of `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType`, add `Akeneo\Component\Batch\Job\JobParametersFactory` argument
- Move and rename `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat\AssetStandardConverter` to `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard\Asset`
- Move and rename `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat\TagStandardConverter` to `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard\Tag`
- Move and rename `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat\ChannelConfigurationStandardConverter` to `PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard\ChannelConfiguration`
- Move and rename `PimEnterprise\Component\Security\Connector\ArrayConverter\Flat\LocaleAccessesStandardConverter` to `PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard\LocaleAccesses`
- Move and rename `PimEnterprise\Component\Security\Connector\ArrayConverter\Flat\AssetCategoryAccessesStandardConverter` to `PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard\AssetCategoryAccesses`
- Move and rename `PimEnterprise\Component\Security\Connector\ArrayConverter\Flat\ProductCategoryAccessesStandardConverter` to `PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard\ProductCategoryAccesses`
- Move and rename `PimEnterprise\Component\Security\Connector\ArrayConverter\Flat\AttributeGroupAccessesStandardConverter` to `PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard\AttributeGroupAccesses`
- Move and rename `PimEnterprise\Component\Security\Connector\ArrayConverter\Flat\JobProfileAccessesStandardConverter` to `PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard\JobProfileAccesses`
- Move and rename `PimEnterprise\Component\Workflow\Connector\ArrayConverter\Flat\ProductDraftStandardConverter` to `PimEnterprise\Component\Workflow\Connector\ArrayConverter\FlatToStandard\ProductDraft`
- Remove parameter `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface` from constructor of `PimEnterprise\Component\Workflow\Connector\Processor\Denormalization\ProductDraftProcessor`
- Remove `PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization\AssetProcessor` and `PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization\TagProcessor`
- Remove `PimEnterprise\Component\Security\Connector\Denormalization\AssetCategoryAccessProcessor`, `PimEnterprise\Component\Security\Connector\Denormalization\AttributeGroupAccessProcessor`, `PimEnterprise\Component\Security\Connector\Denormalization\JobProfileAccessProcessor`, `PimEnterprise\Component\Security\Connector\Denormalization\LocaleAccessProcessor` and `PimEnterprise\Component\Security\Connector\Denormalization\ProductCategoryAccessProcessor`
- Remove `PimEnterprise\Component\ProductAsset\Connector\Step\TagStep`
- Remove `PimEnterprise\Component\Security\Connector\Writer\AccessesWriter`
- Change constructor of `PimEnterprise\Component\ProductAsset\Connector\Processor\Denormalization\ChannelConfigurationProcessor`, remove `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface` parameter
- Change constructor of `PimEnterprise\Component\ProductAsset\Updater\AssetUpdater`, add `PimEnterprise\Component\ProductAsset\Factory\AssetFactory` as last parameter
- Remove `PimEnterprise\Component\CatalogRule\Validator\SupportedOperatorConditionValidator`
- Change `PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraint\ExistingFilterField` from property to class constraint
- Change constructor of `PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`, add string as parameter in the constructor, which is the form type as last parameter
- Rename method `Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface::getBatchJobCode` into `getJobInstanceCode`
- Change constructor of `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Publish`, add string as parameter in the constructor, which is the instance job code
- Change constructor of `PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Unpublish`, add string as parameter in the constructor, which is the instance job code
- Change constructor of `Akeneo\Bundle\RuleEngineBundle\Runner\ChainedRunner`, add `Akeneo\Bundle\RuleEngineBundle\Runner\RunnerRegistryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Remove `PimEnterprise\Component\ProductAsset\Remover\AssetRemover`
- Removed event `pimee_product_asset.pre_remove.asset`, use `akeneo.storage.pre_remove` instead
- Removed event `pimee_product_asset.post_remove.asset`, use `akeneo.storage.post_remove` instead
- Removed `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductReader`, use `Pim\Component\Connector\Reader\Database\ProductReader` instead
