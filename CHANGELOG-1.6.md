# 1.6.19 (2017-09-05)

# 1.6.18 (2017-07-17)

## Bug fixes

- PIM-6529: allow more than 100 elements to be exported with YAML export
- PIM-6526: Prevent MySQL exclusive locks on completeness calculation requests

# 1.6.17 (2017-06-30)

## Bug fixes

- PIM-6473: Fix not visible attributes in the attribute popin on export builder (firefox only)
- PIM-6433: Fix product import reference data multi with duplicate code options

# 1.6.16 (2017-05-24)

## Bug fixes

- PIM-6418: Fix URL too long in variant group datagrid
- GITHUB-5451: Update akeneo_batch.yml sender email, cheers @julienquetil!

# 1.6.15 (2017-05-18)

## Bug fixes

- PIM-6376: Fix slow MongoDB queries when editing attribute options

# 1.6.14 (2017-04-21)

## Bug fixes

- PIM-6375: Fix empty tags and content not being saved in wysiwyg

# 1.6.13 (2017-03-29)

## Bug fixes

- PIM-6241: add control on attribute having same code as association type

# 1.6.12 (2017-02-28)

## Bug fixes

- PIM-6162: Fix families rendering when editing product export

## Improvements

- TIP-500: Dispatch events during installation process

# 1.6.11 (2017-02-14)

## Improvements

- PIM-6149: Remove version number displayed on login page
- PIM-6157: Improve product exports speed
- PIM-6159: Fix number comparator when removing numeric value in the PEF

## Bug fixes

- PIM-6152: Fix fatal error on import in case of wrong column count

# 1.6.10 (2017-02-02)

## Bug fixes

- PIM-6113: Fix wrong context switch on association groups
- PIM-6062: Fix potential Unix commands injection
- PIM-5085: Fix update of the normalized data on family update (mongodb)
- PIM-6080: Fix simple and multi select removal on product export builder
- PIM-6124: Importing family with missing requirement column does not remove associated requirements anymore
- PIM-6140: Fix 'equals to' filters on job tracker page

# 1.6.9 (2017-01-17)

## Bug fixes

- PIM-6110: Saving a product value clears the saved associations
- PIM-6086: Command that removes obsolete relations and migrates normalizedData for MongoDB documents.

# 1.6.8 (2017-01-05)

## Bug fixes

- PIM-6033: Fix validation issue when you add a blank attribute option line
- PIM-6042: Successfully import product associations without removing already existing associations when option "compare values" is set to true
- PIM-6041: Fix wrong conversion units output for channel export profiles csv and xlsx
- PIM-6047: Do not export conversion units of channels if no conversion is set
- PIM-6038: Fix product imports that do not change the product update date correctly (mongodb)

# 1.6.7 (2016-12-20)

## Bug fixes

- PIM-6031: Deleted families are still visible on the product grid
- PIM-6025: Fix a bug that prevents to completely change the channel's locales

# 1.6.6 (2016-12-08)

## Bug fixes

- PIM-6027: Fix export builder filter on category with code as integer
- PIM-6018: Prevent the import of an attribute identifier if not usable as grid filter
- PIM-6022: Fix shell command injection in mass-edit

# 1.6.5 (2016-11-25)

## Bug fixes

- PIM-5994: fix duplication of attributes in the product export
- PIM-6000: Fix migration from 1.5 on quick exports
- #5198: Fix issue with the export builder time condition, cheers @Schwierig
- #5192: Add upgrade script for missing export job, cheers @masmrlar!
- PIM-6017: Fix attribute options on localizable and scopable attributes multi select

# 1.6.4 (2016-10-20)

## Bug fixes

- #4987: Fix hardcoded image URLs in the dashboard, cheers @julienanquetil!
- PIM-5982: Missing job instance parameters when using custom configuration in command line
- PIM-5977: Fix missing products in the variant group edit form
- PIM-5893: Fix products and assets category display issue on Firefox
- PIM-5536: Fix search of an attribute by its code
- #5129: Remove useless "league/flysystem-sftp" dependency, if you use "League\Flysystem\Sftp\SftpAdapter" in your own project code, please add this dependency in the composer.json of your project, cheers @mathewrapid!

# 1.6.3 (2016-09-22)

## Bug fixes

- PIM-5947: Add default file path in new export jobs
- PIM-5964: Index category labels by locale code in channel normalization
- PIM-5968: Fix default translation for attribute options when value is null
- PIM-5897: Fix the does not contain filter to filter on product without product values
- PIM-5566: Fix version number displayed as decimals
- PIM-5976: Fix adding products to a group (regression following variant group ajaxification)
- PIM-5975 & #5016: Fix attribute groups order not kept in product edit form, cheers @julienanquetil!
- #4994: Fix grid filter selection in the user profile, cheers @julienanquetil!
- PIM-5978: Fix missing currencies
- #4993: Fix XLSX product import with numeric value for simpleselect codes, cheers @julienanquetil!

## Functionnal improvements

- PIM-5782: User can now change the attribute order on the product export builder

# 1.6.2 (2016-09-02)

## Bug fixes

- PIM-5958: Fix tooltip for the field "attributes" in the tab "Content" of an export profile
- PIM-5963: Fix installation on MySQL 5.7

# 1.6.1 (2016-09-01)

## Bug fixes

- PIM-5935: Fix view all button in dashboard
- PIM-5945: Fix tabs on user profile, DOM was not well structured
- TIP-568: Detach version entity to improve performances on products import
- PIM-5940: Add a flash message in case of product export builder error
- PIM-5922: Fix the scope switcher in variant group page grid
- PIM-5959: Change wording of XLSX to Excel on mass actions
- PIM-5938: Fix typos in import/export tooltips
- PIM-5966: Fix style about category filter on product export builder
- PIM-5952: Add the information "No condition on families" in the family field of the Export Builder
- PIM-5965: Fix the display order of the default product export filters

# 1.6.0 (2016-08-30)

## Bug fixes

# 1.6.0-RC1 (2016-08-29)

## Bug fixes

- #4879: Fix collision when using several popins on the same page, cheers @dimitri-koenig!
- PIM-5928: Export products without media

## Functional improvements

- PIM-5701: Add CSV and XLSX import jobs for currencies, channels, locales, group types and attribute groups

# 1.6.0-ALPHA2 (2016-08-23)

## Bug fixes

- PIM-5915: Fix the import of localizable and scopable variant group attributes
- PIM-5929: Fix the validation issue indicator appearance on form tabs

# 1.6.0-ALPHA1 (2016-08-01)

## Bug fixes

- PIM-5854: The family code is not displayed at all in the product grid when no family labels
- PIM-5888: Fix an outline glitch on some buttons
- PIM-5869: Allow any codes to be used for attributes
- PIM-5915: Fix the import of localizable and scopable variant group attributes
- PIM-5852: Fix sort order overwriting when importing existing attribute options without sort order column
- PIM-5852: Fix sort order overwriting when importing existing attribute options without sort order column

## Functional improvements

### Support Excel Files

- PIM-5645: As Peter, I would like to have a dedicated connector for Excel files
- PIM-5435: As Mary, I would like to import and export products with Excel files
- PIM-5100: As Peter, I would like to export variant groups and groups in Excel files
- PIM-5095: As Peter, I would like to import variant groups and groups with Excel files
- PIM-5099: As Peter, I would like to export the catalog structure in Excel files
- PIM-5097: As Peter, I would like to import the catalog structure in Excel files
- PIM-5098: As Mary, I would like to export products in Excel files
- PIM-5096: As Mary, I would like to quick export products in Excel files
- PIM-5094: As Mary, I would like to download the invalid data in Excel files
- PIM-5093: As Mary, I would like to import products with Excel files
- PIM-5692: As Mary, I would like to import localized Excel files
- PIM-5641: As Mary, I would like to define a limit number of lines for the Excel files in order to manage them easily in Excel
- PIM-5612: As Peter, I would like to know the Excel versions supported by our Excel connector

###  Export Builder

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
- PIM-5798: As Peter, I would like to manage permission on "Content" tab

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
- PIM-5099: The catalog structure can now be exported in XLSX format (families, attributes, attribute options, association types and categories)
- PIM-5097: The catalog structure can now be imported in XLSX format (families, attributes, attribute options, association types and categories)
- PIM-5657: It is now possible to add custom tabs within the job profile and edit pages
- PIM-5700: Move the channel our of the association grid filter
- PIM-5594: As Julia, when I edit in sequential some products, I would like to keep the completeness panel displayed
- PIM-5781: Add new data on the "system information" screen (data volumetry, information about the operating system)
- PIM-5736: As Mary, I would like to have different bulk actions to facilitate the use
- PIM-5742: Improve the products save for mass operations, I would like to have the completeness and rules calculated directly
- PIM-5577: As Julia, when I save several products, I would like to have the completeness and rules calculated directly

## Scalability improvements

- PIM-5542: Optimize the Family normalization
- PIM-5401: Revamp the Variant Group Form to use the Product Edit Form System and supports thousands of attributes in the selection popin

## Technical improvements

- PIM-5589: Introduce a channels, attribute groups, group types, locales and currencies import using the new import system introduced in v1.4
- PIM-5589: Introduce a SimpleFactoryInterface to create simple entities
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

- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\GroupSaver`. Add `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`.
- Remove services `pim_serializer.normalizer.flat.*`, `pim_serializer.denormalizer.flat.*` and `pim_reference_data.denormalizer.flat.`
- Change constructor of `Pim\Component\Catalog\Normalizer\Structured\ProductValueNormalizer`. Remove argument `Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface`
- Add method `convertToLocalizedFormats` to `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`
- Remove `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`. Please use `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductProcessor`
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\GroupNormalizer`. Add `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer`. Add `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`
- Change constructor of `Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor`. Remove second argument `Symfony\Component\Serializer\Normalizer\DenormalizerInterface` and replace fourth and fifth argument by `Pim\Component\Connector\Processor\BulkMediaFetcher` and `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
- Move `Pim\Bundle\BaseConnectorBundle\DependencyInjection\Compiler\RegisterArchiversPass` to `Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler\RegisterArchiversPass`
- Move `Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector` to `Pim\Bundle\ConnectorBundle\EventListener\InvalidItemsCollector`
- Move `Pim\Bundle\BaseConnectorBundle\EventListener\JobExecutionArchivist` to `Pim\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist`
- Move `Pim\Bundle\BaseConnectorBundle\Archiver\AbstractFilesystemArchiver` to `Pim\Component\Connector\Archiver\AbstractFilesystemArchiver`
- Move `Pim\Bundle\BaseConnectorBundle\Archiver\ArchivableFileWriterArchiver` to `Pim\Component\Connector\Archiver\ArchivableFileWriterArchiver`
- Move `Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface` to `Pim\Component\Connector\Archiver\ArchiverInterface`
- Move `Pim\Bundle\BaseConnectorBundle\Archiver\FileWriterArchiver` to `Pim\Component\Connector\Archiver\FileWriterArchiver`
- Move `Pim\Bundle\BaseConnectorBundle\Archiver\ZipFilesystemFactory` to `Pim\Component\Connector\Archiver\ZipFilesystemFactory`
- Move `Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel` to `Pim\Component\Connector\Validator\Constraints\Channel`
- Move `Pim\Bundle\BaseConnectorBundle\Validator\Constraints\ChannelValidator` to `Pim\Component\Connector\Validator\Constraints\ChannelValidator`
- Change constructor of `Pim\Component\Catalog\Normalizer\Structured\ProductNormalizer`. It now takes two `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as arguments (one for the properties and one for the associations).
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor`. Add `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter` and `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` as last arguments.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`. Add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` as last arguments.
- Rename `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType` to `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType`
- Rename method `getRawConfiguration` to `getRawParameters` in `Akeneo\Component\Batch\Model\JobInstance`
- Rename method `setRawConfiguration` to `setRawParameters` in `Akeneo\Component\Batch\Model\JobInstance`
- Change constructor of `Akeneo\Component\Buffer\BufferInterface`. Add `$options` array as the second argument.
- Move `Pim\Component\Connector\Writer\File\CsvWriter` to `Pim\Component\Connector\Writer\File\Csv\Writer`
- Move `Pim\Component\Connector\Writer\File\CsvProductWriter` to `Pim\Component\Connector\Writer\File\Csv\ProductWriter`
- Move `Pim\Component\Connector\Writer\File\CsvVariantGroupWriter` to `Pim\Component\Connector\Writer\File\Csv\VariantGroupWriter`
- Change constructor of `Pim\Component\Connector\Processor\Denormalization\ProductProcessor`. Remove argument `Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface`.
- Add method `findPotentiallyPurgeableBy` to interface `Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface`
- Add method `getNewestVersionIdForResource` to interface `Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface`
- Move `Pim\Component\Connector\Writer\Doctrine` to `Pim\Component\Connector\Writer\Database`
- Move `Pim\Component\Connector\Reader\ProductReader` to `Pim\Component\Connector\Reader\Database\ProductReader` and remove `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` from constructor.
- Move `Pim\Component\Connector\Reader\Doctrine\BaseReader` to `Pim\Component\Connector\Reader\Database\BaseReader`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor`. Add `Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface` as the fourth argument.
- Change constructor of `Pim\Component\Catalog\Factory\GroupFactory`. Add `Pim\Component\Catalog\Factory\ProductTemplateFactory` as the second argument.
- Remove `Pim\Component\Connector\Writer\File\SimpleFileWriter` as it was not used
- Move `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility` to `Pim\Bundle\CatalogBundle\ProductQueryUtility`
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor` . Add `Pim\Component\Connector\ArrayConverter\Flat\Product\FieldSplitter`
- Change constructor of `Pim\Component\Connector\Reader\ProductReader`. Add `Akeneo\Component\Batch\Job\JobRepositoryInterface`.
- Add method `getLastJobExecution` to interface `Akeneo\Component\Batch\Job\JobRepositoryInterface`
- Remove properties editTemplate, showTemplate from `src\Akeneo\Component\Batch\Job\Job`.
- Remove methods setShowTemplate, setEditTemplate from `src\Akeneo\Component\Batch\Job\Job`.
- Change constructor of `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`. Add `Akeneo\Bundle\BatchBundle\Connector\JobTemplateProviderInterface`
- Change constructor of `Pim\Component\Connector\Writer\File\Csv\Writer` . Add parameter `Pim\Component\Connector\Writer\File\FlatItemBufferFlusher`
- Change constructor of `Pim\Component\Connector\Writer\File\Csv\ProductWriter` . Add parameter `Pim\Component\Connector\Writer\File\FlatItemBufferFlusher`, `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`, `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`, `Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface` and array `$mediaAttributeTypes`
- Change constructor of `Pim\Component\Connector\Writer\File\Csv\VariantGroupWriter` . Add parameter `Pim\Component\Connector\Writer\File\FlatItemBufferFlusher`, `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`, `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`, `Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface` and array `$mediaAttributeTypes`
- Remove method `setAvailableLocales` in `Pim\Component\Catalog\Model\AttributeInterface` and `Pim\Component\Catalog\Model\AbstractAttribute`
- `Pim\Component\Connector\Writer\File\FlatItemBuffer` implements `\Countable`
- `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter` does not implement `Pim\Component\Catalog\Query\Filter\FieldFilterInterface`
- `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter` does not implement `Pim\Component\Catalog\Query\Filter\FieldFilterInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter`. Remove the third parameter `supportedFields`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter`. Remove the third parameter `supportedFields`
- Remove `Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager`
- Remove methods `getTreesQB` and `getAllChildrenQueryBuilder` in `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Remove method `getItemIdsInCategory` in `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`
- Replace all parameters in `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface::getItemsCountInCategory()` by `array $categoryIds`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController`. Remove `Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ChannelType`. Add `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface`
- Change constructor of `Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`. Add `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface`
- Remove deprecated methods `getProductCountByTree` and `getProductsCountInCategory` in `Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController`. Add Symfony validator.
- Change constructor of `Pim\Bundle\NotificationBundle\Controller\NotificationController`. Remove deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and add `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface`.
- Change constructor of `Pim\Bundle\NotificationBundle\EventSubscriber\JobExecutionNotifier`. Remove deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and add `Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry` and `Pim\Bundle\NotificationBundle\NotifierInterface`.
- Change constructor of `Pim\Bundle\NotificationBundle\Twig\NotificationExtension`. Replace deprecated `Pim\Bundle\NotificationBundle\Manager\NotificationManager` by `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FileController`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`.
- Change constructor of `Pim\Bundle\EnrichBundle\Imagine\Loader\FlysystemLoader`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`.
- Change constructor of `Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`.
- Change constructor of `Pim\Component\Connector\Writer\File\FileExporter`. Replace `League\Flysystem\MountManager` by `Akeneo\Component\FileStorage\FilesystemProvider`.
- Move `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry` to `Pim\Component\Catalog\AttributeTypeRegistry`
- Move `Pim\Bundle\CatalogBundle\Factory\AttributeFactory` to `Pim\Component\Catalog\Factory\AttributeFactory`
- Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface` twice.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeOptionController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface` and `Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Normalizer\AttributeOptionNormalizer`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager` and add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`.
- Move `Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory` to `Pim\Component\Catalog\Factory\AttributeRequirementFactory`
- Move `Pim\Bundle\CatalogBundle\Factory\GroupFactory` to `Pim\Component\Catalog\Factory\GroupFactory`
- Move `Pim\Bundle\CatalogBundle\Factory\FamilyFactory` to `Pim\Component\Catalog\Factory\FamilyFactory`
- Move `Pim\Bundle\CatalogBundle\Factory\MetricFactory` to `Pim\Component\Catalog\Factory\MetricFactory`
- Move `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` to `Pim\Component\Catalog\Manager\CompletenessManager`
- Move `Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager` to `Pim\Component\Catalog\Manager\AttributeGroupManager`
- Move `Pim\Bundle\CatalogBundle\Manager\VariantGroupAttributesResolver` to `Pim\Component\Catalog\Manager\VariantGroupAttributesResolver`
- Move `Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplier` to `Pim\Component\Catalog\Manager\ProductTemplateApplier`
- Move `Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder` to `Pim\Component\Catalog\Builder\ProductTemplateBuilder`
- Move `Pim\Bundle\CatalogBundle\Builder\ProductBuilder` to `Pim\Component\Catalog\Builder\ProductBuilder`
- Move `Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver` to `Pim\Component\Catalog\Manager\AttributeValuesResolver`
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\FamilyManager`.
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\ProductManager`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\DeleteProductsMassActionHandler`. Remove `Pim\Bundle\CatalogBundle\Manager\ProductManager`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\AttributeType`. Replace `Pim\Bundle\CatalogBundle\Manager\AttributeManager` by `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeManager`. Add `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry` and `Pim\Bundle\CatalogBundle\Factory\AttributeFactory`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\AttributeOptionController`. Remove `Pim\Bundle\CatalogBundle\Manager\AttributeManager`. Add `Pim\Bundle\CatalogBundle\Manager\AttributeOptionsSorter` and `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`.
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\AttributeManager`.
- Move `Pim\Bundle\CatalogBundle\Query\Filter\DumperInterface` to `Pim\Bundle\CatalogBundle\Command\DumperInterface`
- Move `Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterDumper` to `Pim\Bundle\CatalogBundle\Command\ProductQueryHelp\AttributeFilterDumper`
- Move `Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterDumper` to `Pim\Bundle\CatalogBundle\Command\ProductQueryHelp\FieldFilterDumper`
- Move namespace `Pim\Bundle\CatalogBundle\Query` to `Pim\Component\Catalog\Query`
- Move namespace `Pim\Bundle\CatalogBundle\Exception` to `Pim\Component\Catalog\Exception`
- Move `Pim\Bundle\CatalogBundle\Event\ProductEvents` to `Pim\Component\Catalog\ProductEvents`
- Move namespace `Pim\Bundle\CatalogBundle\Repository` to `Pim\Component\Catalog\Repository`
- Remove deprecated `Pim\Bundle\CatalogBundle\Manager\CurrencyManager`. Please use the service `pim_catalog.repository.currency` instead of `@pim_catalog.manager.currency`.
- Change constructor of `Pim\Bundle\CatalogBundle\AttributeType\PriceCollectionType`. Remove `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\PriceFilter`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Validator\Constraints\CurrencyValidator`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Change constructor of `Pim\Bundle\FilterBundle\Form\Type\Filter\PriceFilterType`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`.
- Move namespace `Pim\Bundle\CatalogBundle\Validator` to `Pim\Component\Catalog\Validator`
- Move `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes` to `Pim\Component\Catalog\AttributeTypes`
- Remove method `getCategoryIds` in `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- Installer fixtures now support csv format for attribute groups setup and not anymore the yml format
- Installer fixtures now support csv format for group types setup and not anymore the yml format
- Installer fixtures now support csv format for locales setup and not anymore the yml format
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
- Remove argument $em from constructor of `Pim\Bundle\NotificationBundle\Manager\NotificationManager` and inject `Akeneo\Component\StorageUtils\Saver\SaverInterface` and `Akeneo\Component\StorageUtils\Remover\RemoverInterface`. Replace `Doctrine\ORM\EntityRepository` by `Pim\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface` and remove `Pim\Bundle\NotificationBundle\Factory\NotificationFactory`.
- Rename createFamily to create in the `Pim\Bundle\CatalogBundle\Factory\FamilyFactory`
- Remove createUser from the `Oro\Bundle\UserBundle\Entity\UserManager`. You can now use the SimpleFactory to create new users
- Remove `Pim\Component\Catalog\Factory\ChannelFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Remove `Akeneo\Component\Classification\Factory\CategoryFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- Remove `Pim\Bundle\CatalogBundle\Factory\AssociationTypeFactory` and replaced it by `Akeneo\Component\StorageUtils\Factory\SimpleFactory`
- `Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface` now extends `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Connector\Connector`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Entity\FieldMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Entity\ItemMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Job\BatchException`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Transform\Mapping\FieldMapping`
- Remove deprecated class `Akeneo\Bundle\BatchBundle\Transform\Mapping\ItemMapping`
- Removed deprecated class `Pim\Bundle\CatalogBundle\Manager\ChannelManager`.
- Remove the extend of the `Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController` and `Pim\Bundle\EnrichBundle\AbstractController\AbstractController`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\ProductProcessor` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\ODMProductReader` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\ORMProductReader` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Component\Connector\Validator\Constraints\ChannelValidator` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\CompletenessRepository` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ChannelRepository` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Factory\FamilyFactory` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\ChannelManager` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor` replace `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CompletenessController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`, add `Doctrine\Common\Persistence\ObjectManager` and the parameter `pim_catalog_product_storage_driver` (string).
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\FamilyController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeRequirementsSubscriber` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ProductTemplateType` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\EnrichBundle\Twig\ChannelExtension` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\InstallerBundle\DataFixtures\ORM\LoadUserData` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Bundle\UserBundle\EventSubscriber\UserPreferencesSubscriber` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Change constructor of `Pim\Component\Catalog\Repository\ChannelRepositoryInterface` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Rename `Pim\Component\Catalog\Repository\ChannelRepositoryInterface::getChannelChoices` to `Pim\Component\Catalog\Repository\ChannelRepositoryInterface::getLabelsIndexedByCode`
- Change constructor of `Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository` to inject two more arguments `%akeneo_batch.entity.job_instance.class%` and `%pim_import_export.repository.job_instance.class%`
- Move namespace `Pim\Bundle\TransformBundle\Normalizer\Flat` to `Pim\Bundle\VersioningBundle\Normalizer`
- Move namespace `Pim\Bundle\TransformBundle\Denormalizer\Flat` to `Pim\Bundle\VersioningBundle\Denormalizer`
- Move namespace `Pim\Bundle\TransformBundle\Normalizer\Structured` to `Pim\Component\Catalog\Normalizer\Structured`
- Move namespace `Pim\Bundle\TransformBundle\Denormalizer\Structured` to `Pim\Component\Catalog\Denormalizer\Structured`
- Move and rename class `Pim\Bundle\TransformBundle\DependencyInjection\Compiler\SerializerPass` to `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass`
- Move class `Pim\Bundle\TransformBundle\Cache\CacheClearer` to `Pim\Bundle\BaseConnectorBundle\Cache\CacheClearer`
- Move class `Pim\Bundle\TransformBundle\Cache\DoctrineCache` to `Pim\Bundle\BaseConnectorBundle\Cache\DoctrineCache`
- Move class `Pim\Bundle\TransformBundle\Converter\MetricConverter` to `Pim\Bundle\BaseConnectorBundle\Converter\MetricConverter`
- Remove namespace `Pim\Bundle\BaseConnectorBundle\Exception`
- Remove `TransformBundle`
- Change constructor of `Pim\Component\Catalog\Updater\GroupUpdater` and `Pim\Component\Catalog\Updater\VariantGroupUpdater`, add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Change constructor of `Akeneo\Bundle\BatchBundle\Job\Pim\Bundle\TransformBundle\Normalizer\Structured\FamilyNormalizer` to inject two more dependendies `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` and `Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface`
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
- Add mandatory arguments `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface` in constructor of `Akeneo\Component\Batch\Job\Job`
- Remove methods `setEventDispatcher`, `setJobRepository` and `setName` from `Akeneo\Component\Batch\Step\AbstractStep`
- Add mandatory arguments `Akeneo\Component\Batch\Job\JobRepositoryInterface` and `Symfony\Component\EventDispatcher\EventDispatcherInterface` in constructor of `Akeneo\Component\Batch\Step\AbstractStep`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Reader\DummyReader`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Validator\Import\ImportValidatorInterface`
- Remove deprecated `Pim\Bundle\BaseConnectorBundle\Validator\Import\SkipImportValidator`
- Remove `Pim\Bundle\InstallerBundle\Command\LoadDataFixturesDoctrineCommand`, `Pim\Bundle\InstallerBundle\Command\LoadFixturesCommand`
- Remove `Pim\Bundle\InstallerBundle\DataFixtures\*`
- Remove `Pim\Bundle\InstallerBundle\FixtureLoader\*`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter`, add `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`
- Remove `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Remove `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\GroupController`
    replace `Pim\Bundle\CatalogBundle\Manager\GroupManager` by `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\VariantGroupController`
    replace `Pim\Bundle\CatalogBundle\Manager\GroupManager` by `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
    add `Pim\Bundle\UserBundle\Context\UserContext`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`
    replace `Pim\Bundle\CatalogBundle\Manager\CategoryManager` by `Pim\Component\Catalog\Repository\CategoryRepositoryInterface`
    remove the parameter `$categoryClass`
    remove method `getTrees()`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType`
    replace `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
    remove `Pim\Bundle\UserBundle\Context\UserContext`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ChannelType`
    replace `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\ProductEditType`
    replace `Pim\Component\Catalog\Repository\FamilyRepositoryInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
- Change constructor of `Pim\Bundle\UserBundle\Form\Subscriber\UserPreferencesSubscriber`
    replace `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface` by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` remove `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Remove interface `Pim\Bundle\EnrichBundle\Form\DataTransformer\ChoicesProviderInterface` (replace by `Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface`)
- Remove class `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Remove class `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Remove method `findAllAxis` from `Pim\Component\Catalog\RepositoryAttributeGroupRepositoryInterface`
- Remove method `getChoices` from `Pim\Component\Catalog\GroupRepositoryInterface`
- Remove method `getAvailableAttributesAsLabelChoice` from `Pim\Component\Catalog\AttributeRepositoryInterface`
- Rename method `findAllAxis`in `findAvailableAxes` from `Pim\Component\Catalog\AttributeRepositoryInterface`
- Rename method `findAllAxisQB` in `findAllAxesQB` from `Pim\Component\Catalog\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements`, add batch job code (string)
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`, add `Symfony\Component\Security\Core\User\UserProviderInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Context option `filter_type` of `Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductNormalizer` changed to `filter_types` and now accepts an array of filter names instead of just one filter name
- Context option `filter_type` of `Pim\Component\Catalog\Normalizer\Structured\ProductNormalizer` changed to `filter_types` and  now accepts an array of filter names instead of just one filter name
- Remove methods `getConfigurationFields()`, `getConfiguration()` and `setConfiguration()` from `Akeneo\Component\Batch\Item\AbstractConfigurableStepElement`
- Remove methods `getConfiguration()` and `setConfiguration()` from `Akeneo\Component\Batch\Job\Job`
- Add argument `Akeneo\Component\Batch\Job\JobParameters` in method `createJobExecution()` of `Akeneo\Component\Batch\Job\JobRepositoryInterface`
- Remove methods `getConfiguration()`, `setConfiguration()` and `getConfigurableStepElements()` from `Akeneo\Component\Batch\Step\StepInterface`
- Remove methods `getConfiguration()`, `setConfiguration()` and `getConfigurableStepElements()` from `Akeneo\Component\Batch\Step\AbstractStep`
- Remove methods `getConfiguration()`, `setConfiguration()`, `setReader()`, `setProcessor()`, `setWriter()`, `setBatchSize()` from `Akeneo\Component\Batch\Step\ItemStep`
- Change constructor of `Pim\Component\Connector\Processor\Denormalization\JobInstanceProcessor` to add argument `Akeneo\Component\Job\JobRegistry`
- Change constructor of `Akeneo\Component\Batch\Updater\JobInstanceUpdater` to add argument `Akeneo\Component\Job\JobRegistry`
- Change constructor of `Pim\Component\Connector\Archiver\ArchivableFileWriterArchiver` to add argument `Akeneo\Component\Job\JobRegistry`
- Change constructor of `Pim\Component\Connector\Archiver\FileReaderArchiver` to add argument `Akeneo\Component\Job\JobRegistry`
- Change constructor of `Pim\Component\Connector\Archiver\FileWriterArchiver` to add argument `Akeneo\Component\Job\JobRegistry`
- Change constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add argument `Akeneo\Component\Job\JobRegistry`
- Change constructor of `Akeneo\Bundle\BatchBundle\Validator\Constraints\JobInstanceValidator` to replace argument `Akeneo\Component\Connector\ConnectorRegistry` by `Akeneo\Component\Job\JobRegistry`
- Remove argument array $configuration from `Pim\Component\Connector\Step\TaskletInterface::execute()`, we can access to the JobParameters from the StepExecution $stepExecution
- Change constructor of `Pim\Component\Catalog\Updater\AttributeUpdater`, remove `Pim\Component\ReferenceData\ConfigurationRegistryInterface` and the list of reference data types
- Move class `Pim\Component\Catalog\Normalizer\Structured\ReferenceDataNormalizer` to `Pim\Component\ReferenceData\Normalizer\Structured\ReferenceDataNormalizer`
- Move class `Pim\Component\Connector\Normalizer\Flat\ReferenceDataNormalizer` to `Pim\Component\ReferenceData\Normalizer\Flat\ReferenceDataNormalizer`
- Move class `Pim\Component\Catalog\Denormalizer\Structured\ProductValue\ReferenceDataDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Structured\ProductValue\ReferenceDataDenormalizer`
- Move class `Pim\Component\Catalog\Denormalizer\Structured\ProductValue\ReferenceDataCollectionDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Structured\ProductValue\ReferenceDataCollectionDenormalizer`
- Move class `Pim\Component\Connector\Denormalizer\Flat\ProductValue\ReferenceDataDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue\ReferenceDataDenormalizer`
- Move class `Pim\Component\Connector\Denormalizer\Flat\ProductValue\ReferenceDataCollectionDenormalizer` to `Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue\ReferenceDataCollectionDenormalizer`
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\QuickExport\ProductToFlatArrayProcessor`, add `Symfony\Component\Security\Core\User\UserProviderInterface` and `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Context option `filter_type` of `Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductNormalizer` changed to `filter_types` and now accepts an array of filter names instead of just one filter name
- Context option `filter_type` of `Pim\Component\Catalog\Normalizer\Structured\ProductNormalizer` changed to `filter_types` and  now accepts an array of filter names instead of just one filter name
- Move class `Pim\Component\Catalog\Normalizer\Structured\JobInstanceNormalizer` to `Akeneo\Component\Batch\Normalizer\Structured\JobInstanceNormalizer`
- Change constructor of `Pim\Component\Catalog\Factory\AttributeRequirementFactory` to inject `%pim_catalog.entity.attribute_requirement.class%`
- Change constructor of `Pim\Component\Catalog\Localization\Presenter\MetricPresenter`, replace argument `Symfony\Component\Translation\TranslatorInterface` by `Akeneo\Component\Localization\TranslatorProxy`
- Change constructor of `Pim\Component\Catalog\Builder\ProductTemplateBuilder`, remove argument `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver`
- Add `$locale` argument to method `addAttributes` in `Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\VariantGroupAttributeController`, add `Pim\Bundle\EnrichBundle\Resolver\LocaleResolver` argument
- Change constructor of `Pim\Component\Catalog\Denormalizer\Structured\ProductValuesDenormalizer`, remove argument `%pim_catalog.entity.attribute.class%` and
    replace argument `Doctrine\Common\Persistence\ManagerRegistry` by `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Change constructor of `Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridProvider`, add `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\JobExecutionNormalizer`, add `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\StepExecutionNormalizer`, add `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType`, add `Akeneo\Component\Batch\Job\JobParametersFactory` and `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` arguments
- Change constructor of `Akeneo\Component\Batch\Model\Warning` to remove the $name argument, we also remove related getter/setter
- Remove getName() from `Akeneo\Component\Batch\Item\AbstractConfigurableStepElement`
- Remove $name argument from addWarning method of `Akeneo\Component\Batch\Model\StepExecution`
- Remove `Pim\Bundle\EnrichBundle\Provider\ColorsProvider`
- Remove `Pim\Bundle\EnrichBundle\Twig\ChannelExtension`
- Remove twig functions `channel_color` and `channel_font_color`
- Remove property color from the model `Pim\Bundle\CatalogBundle\Entity\Channel` and interface `Pim\Component\Catalog\Model\ChannelInterface`
- Rename `Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface` to `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`
- Remove `Pim\Component\Connector\ArrayConverter\Structured\AttributeOptionStandardConverter`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\AttributeOptionStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\AttributeOption`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\AttributeStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Attribute`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\ProductAssociationStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductAssociation`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\VariantGroupStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\VariantGroup`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\GroupStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Group`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\CategoryStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Category`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\AssociationTypeStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\AssociationType`
- Move and rename `Pim\Component\Connector\ArrayConverter\Flat\FamilyStandardConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Family`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\ValueConverterRegistry` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterRegistry`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\AbstractValueConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\AbstractValueConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\PriceConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\PriceConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\MetricConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\MetricConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\MultiSelectConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\MultiSelectConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\SimpleSelectConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\SimpleSelectConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\MediaConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\MediaConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\TextConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\TextConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\ScalarConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ScalarConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\FieldConverter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldConverter`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnsResolver` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ColumnsMerger` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\ColumnsMapper` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor`
- Move `Pim\Component\Connector\ArrayConverter\Flat\Product\FieldSplitter` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter`
- Move `Pim\Component\Connector\Reader\File\CsvReader` to `Pim\Component\Connector\Reader\File\Csv\Reader`. Change constructor to add `Pim\Component\Connector\Reader\File\FileIteratorFactory` and `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`.
- Move `Pim\Component\Connector\Reader\File\CsvProductReader` to `Pim\Component\Connector\Reader\File\Csv\ProductReader`. Change constructor to remove `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`, decimalSeparators and dateFormats to add `Pim\Component\Connector\Reader\File\FileIteratorFactory`, `Pim\Component\Connector\Reader\File\MediaPathTransformer` and `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface`.
- Move `Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader` to `Pim\Component\Connector\Reader\File\Yaml\Reader`. Change constructor to add `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface` as first parameter.
- Remove `Pim\Component\Connector\Processor\Denormalization\AssociationTypeProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove `Pim\Component\Connector\Processor\Denormalization\CategoryProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove `Pim\Component\Connector\Processor\Denormalization\FamilyProcessor` and replaced it by `Pim\Component\Connector\Processor\Denormalization\SimpleProcessor`
- Remove parameter `Pim\Component\Connector\ArrayConverter\ArrayConverterInterface` from constructors of `Pim\Component\Connector\Processor\Denormalization\ProductAssociationProcessor` and `Pim\Component\Connector\Processor\Denormalization\ProductProcessor`.
- Invert the two first arguments or the constructor of `Pim\Component\Connector\Processor\Denormalization\AttributeProcessor`
- Move `Pim\Bundle\BaseConnectorBundle\Processor\Normalization\VariantGroupProcessor` to `Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor`
- Change constructor of `Pim\Component\Catalog\Updater\AttributeUpdater`, add `Pim\Component\Catalog\AttributeTypeRegistry` as last parameter.
- Remove `Pim\Component\Connector\Processor\Denormalization\AttributeOptionProcessor`, `Pim\Component\Connector\Processor\Denormalization\AttributeProcessor`, `Pim\Component\Connector\Processor\Denormalization\GroupProcessor`.
- Add parameter `$operationGroup` to `Pim\Bundle\EnrichBundle\MassEditAction\MassEditFormResolver::getAvailableOperationsForm()`
- Add parameter `$operationGroup` to `Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface::register()`
- Replace method `getAllByGridName()` by `getAllByGridNameAndGroup()` in `Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface`
- Change visibility of `checkValue()` method of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MediaFilter` from public to protected
- Add `getAttributeTypes()` method to `Pim\Component\Catalog\Query\Filter\AttributeFilterInterface`
- Add `getField()` method to `Pim\Component\Catalog\Query\Filter\FieldFilterInterface`
- Add `getAttributeFilters()` and `getFieldFilters()` to `Pim\Component\Catalog\Query\Filter\FilterRegistryInterface`
- Rename method `getAlias` to `getJobName` in `Akeneo\Component\Batch\Model\JobInstance`
- Rename method `setAlias` to `setJobName` in `Akeneo\Component\Batch\Model\JobInstance`
- Remove methods `getJob` and `setJob` in `Akeneo\Component\Batch\Model\JobInstance`
- Remove `Pim\Bundle\EnrichBundle\Controller\MassEditActionController` and replaced it by `Pim\Bundle\EnrichBundle\Controller\MassEdit\ProductController`, `Pim\Bundle\EnrichBundle\Controller\MassEdit\FamilyController`
- Add string parameter `$formName` to `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`
- Remove method `getItemsName()` from `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface` and deleted from all classes implementing the interface
- Remove method `getItemsName()` from `Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface` and deleted from all classes implementing the interface
- Change constructor of `Akeneo\Component\Batch\Job\Job` to add the array $steps argument
- Remove the methods setSteps, addStep from `Akeneo\Component\Batch\Job\Job`
- Remove the class `Akeneo\Component\Batch\Connector\ConnectorRegistry`, please use `Akeneo\Component\Batch\Job\JobRegistry`
- Remove the class `Akeneo\Component\Batch\Step\StepFactory` and related service '@akeneo_batch.step_factory'
- Remove the class `Akeneo\Component\Batch\Job\JobFactory` and related service '@akeneo_batch.job_factory'
- Remove method `setCharsetValidator()` from `Pim\Component\Connector\Step\ValidatorStep`
- Change constructor of `Pim\Component\Connector\Step\ValidatorStep` add `Pim\Component\Connector\Item\CharsetValidator` as last parameter
- Change constructor of `Pim\Component\Connector\Step\TaskletStep` add `Pim\Component\Connector\Step\TaskletInterface` as last parameter
- Change constructor of `Pim\Bundle\EnrichBundle\Connector\Step\MassEditStep` add `Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\TemporaryFileCleaner` as last parameter
- Remove the class `Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver` and replaced by `Pim\Component\Connector\Archiver\CsvInvalidItemWriter` and `im\Bundle\BaseConnectorBundle\Archiver\XlsxInvalidItemWriter`
- Change constructor of `Akeneo\Component\Batch\Event\InvalidItemEvent`.
- Change constructor of `Akeneo\Component\Batch\Item\InvalidItemException`.
- Change method `addWarning()` signature of `Akeneo\Component\Batch\Item\InvalidItemException`.
- Remove the class `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredProductReader`, use `Pim\Component\Connector\Reader\Database\ProductReader` instead.
- Remove the class `Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\VariantGroupCleaner`, logic moved in `Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit\FilteredVariantGroupProductReader`.
- Fifth argument of the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeController` is now mandatory.
- Remove the deprecated interface `Akeneo\Bundle\BatchBundle\ItemUploadedFileAwareInterface`
- Remove deprecated classes `Pim\Bundle\VersioningBundle\Doctrine\AbstractPendingMassPersister`
- Remove deprecated classes `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\PendingMassPersister`
- Remove deprecated classes `Pim\Bundle\VersioningBundle\Doctrine\ORM\PendingMassPersister`
- Remove argument `Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface` from `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover`
- Remove argument `Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface` from `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver`
- Remove argument `Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface` from `CatalogBundle\Doctrine\Common\Saver\AttributeSaver`
- Remove argument `Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface` from `CatalogBundle\Doctrine\Common\Saver\FamilySaver`
- Remove argument `Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface` from `CatalogBundle\Doctrine\Common\Saver\ProductSaver`
- Remove argument `Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface` from `CatalogBundle\Doctrine\MongoDBODM\Saver\ProductSaver`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver`
- Change constructor of `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType`, add `Oro\Bundle\SecurityBundle\SecurityFacade` as last parameter
