# 1.4.27 (2016-08-31)

# 1.4.26 (2016-07-05)

## Bug fixes
- PIM-5863: Fix an issue that prevented the association of multiple products at once in the association tab
- PIM-5389: Fix an issue that would render the history panel multiple times

# 1.4.25 (2016-06-03)

## Bug fixes
- PIM-5820: Fix product value filtering by channel and locale in structured product normalizer
- PIM-5687: Fix an issue that prevented the removal of a product from a variant group in MongoDB
- PIM-5863: Fix an issue that prevented the association of multiple products at once in the association tab
- PIM-5389: Fix an issue that would render the history panel multiple times


# 1.4.24 (2016-05-10)

## Improvements
- PIM-5753: Add a `priority` tag parameter for `ValueConverterRegistry` to allow ordering of array converters.

## BC Breaks
- ValueConverterRegistryInterface: added parameter `$priority` in `register` method to allow priority queue

# 1.4.23 (2016-04-14)

## Scalability improvements
- PIM-5507 : Memory leak during mass edit attributes, mass publish

## BC Breaks
- Changed constructor `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor`
- Added method `hasAttribute` to `Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface`
- Added method `hasAttribute` to `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface`
- Added method `hasAttributeInFamily` to `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface`
- Added method `hasAttributeInVariantGroup` to `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface`

## Bug fixes

- PIM-5725: Fix reference data name of the attribute in case this attribute is not a reference data
- PIM-5699: Fix 'is equal to' operator in export / import history grid filter
- PIM-5650: Fix events binding on PEF grid refresh.

# 1.4.22 (2016-03-23)

## Improvements
- Update akeneo/measure-bundle dependency to version 0.4.1.

## Bug fixes
- PIM-5656: Category Tree does not load when 0 category selected
- PIM-5453: Display completeness missing labels according to the locale your are working on

# 1.4.21 (2016-03-07)

## Bug fixes
- PIM-5587: fix associations checks persisting between two products

# 1.4.20 (2016-02-24)

## Improvements
- PIM-5579: Enable `Pim\Bundle\BaseConnectorBundle\Archiver\FileWriterArchiver` even for exports with several files

## Scalability improvements
- PIM-5575: Remove families JS fetchers warmup

# 1.4.19 (2016-02-11)

## Bug fixes
- PIM-5476: Fix issue with native csv product import, new products are created with extra optional values for media, metric, price
- PIM-5470: Fix Doctrine memory leak
- PIM-5354: Fix select/boolean filter value display after navigation

## Technical improvements
- PIM-5460 Several datepickers: remove jQuery UI datepickers, keep Bootstrap's

# 1.4.18 (2016-01-28)

## Bug fixes
- PIM-5478: Fix attribute permissions issue in attribute searchable repository
- PIM-5492: Fix complete group loading on useless cases
- Fix `Akeneo\Component\Console\CommandLauncher` to launch as a backend task
- PIM-5471: Close & destroy select2 on page change with hash-navigation

# 1.4.17 (2016-01-19)

## Scalability improvements
- PIM-5213: Paginate loading of attributes on Product Edit Form and Mass Edit Common Attributes action

## Bug fixes
- PIM-5021: Forbid the use of code `category` for an attribute
- PIM-5233: Use an asynchronous dropdown list to mass edit family
- PIM-5418: Fix limit on localizable families search
- PIM-5379, PIM-5429: Fix memory leak on MongoDB `ProductSaver` and wrong completeness generation
- PIM-5446: Replace rest attribute configuration action from GET to POST to prevent too long URI

## BC Breaks
- Changed constructor `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ChangeFamilyType` to add `Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface` dependency

# 1.4.16 (2016-01-07)

## Bug fixes
- PIM-5405: Fix content type for stream upload
- PIM-5331: Fix product save issue when categories have code as integer
- PIM-5395: Fix tab redirection on different right cases

## BC Breaks
- Changed constructor `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeController` to add `Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface` dependency

# 1.4.15 (2015-12-30)

## Improvements
- PIM-4897: Added MongoDB status on SF debug toolbar

## Scalability improvements
- PIM-5170: Fix memory leak on bulk detach

## Bug fixes
- PIM-5295: Fix association product grid category filter
- PIM-5348: fix group count on behats
- PIM-5347: fix mongo database in case of attribute removal
- PIM-5387: Fix memory leak in quick export

# 1.4.14 (2015-12-17)

## Scalability improvements
- PIM-5231: Use new AsyncSelectType for family selector in product creation form
- PIM-5232: Load choices asynchronously in the product family filter to improve grid loading time
- PIM-5211: Do not load all axes on the variant group form during edition
- PIM-5210: Use the enhanced Product Edit Form in the "Edit Common Attributes" mass edit action

## BC Breaks
- Changed constructor `Pim\Bundle\EnrichBundle\Form\Type\ProductCreateType` to add `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\FamilyRepository` dependency
- Updated event on which `Pim\Bundle\VersioningBundle\EventSubscriber\AddRemoveSubscriber` subscribing, PRE_REMOVE instead of POST_REMOVE.
- Updated public method preRemove from `Pim\Bundle\VersioningBundle\EventSubscriber\AddRemoveSubscriber` to addRemoveVersion.

## Bug fixes
- PIM-5334: Fix boolean filter on product grid
- PIM-5342: Fix 1.3 to 1.4 migration issue on media thumbnails
- PIM-5202: Fix error message when deleting a product

# 1.4.13 (2015-12-10)

## Scalability improvements
- PIM-5218: Use DirectToMongoDB bulk product saver natively. This considerably speeds up all bulk actions on a MongoDB storage install (imports, mass edit, rules application, etc.).
- PIM-5170: Fixes memory leak on MongoDB at association import time

## BC Breaks
- Changed constructor of `Pim\Bundle\TransformBundle\Normalizer\MongoDB\ProductValueNormalizer`to add a `Doctrine\Common\Persistence\ManagerRegistry` (instead of a DocumentManager to avoid circular references)
  It is required because normalization of reference data in product values is based on Doctrine metadata.
- In case you wrote your own associations import, please add the parameter `batch_size: 1` to the `import_associations` step element of your `batch_jobs.yml`.
- Changed constructor of `Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor` to add a `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`.
  Required, because we now use the standard ProductUpdater to be consistent.
- Changed constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to add
    `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`,
    `Symfony\Component\Validator\Validator\ValidatorInterface`,
    `Symfony\Component\Serializer\Normalizer\NormalizerInterface`.
  Required to raise errors on the enhanced "mass edit common attributes".

## Bug fixes
- PIM-5238: Fix scroll on multiselect for mass edit
- PIM-5177: Fix login redirection
- PIM-5276: Fix attribute ordering in the product view
- PIM-5282: Fix reload freeze
- PIM-5269: Fix date filter picker

# 1.4.12 (2015-12-03)

## Scalability improvements
- PIM-5208: Fix the datagrid performance issue related to large number of attributes, only attribute usable in grid will be available
- PIM-5194: Fix performance issues on families loading in PEF

## Bug fixes
- PIM-5235: Fix empty reference data name on attributes import
- PIM-5215: Create empty product values for new family attributes after product import with family change
- PIM-5268: Fix PDF display to be able to display long attribute name

# 1.4.11 (2015-11-27)

## BC Breaks
- Changed constructor of `Pim\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` in order to avoid circular reference dependency exceptions.

## Scalability improvements
- PIM-5209: Optimize AttributeGroupNormalizer in order to serialize attributes codes in one request.

## Bug fixes
- PIM-5176: Fix customisation of columns not saved in "Column Selection"
- PIM-5201: Fix permission System/Edit a Role
- PIM-5172: Fix PDF export for text area attribute
- PIM-5171: Apply ACLs on mass edit actions
- PIM-5240: Fix Mongo normalization that creates a nullable family field
- PIM-5159: Attribute values appearing/disappearing when you change attribute groups
- PIM-5241: Fix search input field with strange display on Firefox / Mass Edit on common attributes

# 1.4.10 (2015-11-20)

## Bug fixes
- PIM-5163: Fix the VersionRepository on MongoDB to take the most recent entry for product resources
- PIM-5169: Fix mass edit attribute selection while using a small screen resolution

# 1.4.9 (2015-11-12)

## Scalability improvements
- PIM-5127: Improve products export memory usage

## Bug fixes
- PIM-5148: IE11 wrong display on multiple select attributes
- PIM-5161: Fix the is_associated sort on MongoDB association grid
- PIM-5150: Improve the product grid loading performance when a lot of attribute filters are available

# 1.4.8 (2015-11-09)

## Bug fixes
- PIM-5036: Fix metric select2 bug in PEF
- PIM-5119: Fix the "Manage filters", which was very slow when there was a lot of attributes to display
- PIM-5121: Fix the channel code displayed instead of label
- PIM-5139: Fix association grid performance and sorting issues

# 1.4.7 (2015-11-03)

## Bug fixes
- PIM-5079: Add batch jobs script for 1.3 to 1.4 migration
- PIM-5078: Fix category move action
- PIM-4925: Fix dashboard patch available information
- PIM-5082: Fix variant group modal display in product edit form (in mongo storage)
- PIM-5084: Fix attribute groups order in product edit form
- PIM-5008: Optimize product edit form when lots of attributes are used
- PIM-5041: Add cache warmup for product edit form
- PIM-5039: Fix missing translation key for boolean attribute switch

# 1.4.6 (2015-10-27)

## Bug fixes
- PIM-5051: Fix mass delete products error on versionning
- PIM-5055: Fix medias migration for removed medias in product values

# 1.4.5 (2015-10-23)

## Bug fixes
- PIM-4794: Fix static attributes types for reference data
- PIM-5035: Fix products sorting in associations grid
- PIM-5046: Fix identifier attribute not unique on creation

# 1.4.4 (2015-10-16)

## Bug fixes
- PIM-5016: Fix import product with only sku and family columns
- PIM-5017: Fix media migration with lots of files
- PIM-5000: Fix the products on which mass actions are applied
- PIM-5006: Fix the API key generation

# 1.4.3 (2015-10-09)

## Bug fixes
- PIM-4955: Fixed regression on completeness computation when locales are removed from a channel
- PIM-4622: Fix CSS for product comments
- PIM-4973: Fix product removal from edit form (in mongo storage)
- PIM-4977: Revert PIM-4443 by re-allowing full numeric entity codes

# 1.4.2 (2015-10-01)

## Bug fixes
- PIM-4760: Fix error if quick export not well configured
- PIM-4880: Fix media not displayed in product PDF download
- PIM-4887: Fixed locales active status when removed from channels
- PIM-4911: Fix escaping of property with locale and scope
- PIM-4922: Fix media attribute preview
- PIM-4925: Fix dashboard patch information
- PIM-4936: Fixes performances problems and memory leak at import time
- PIM-4935: Fix inconsistent data on import using comparison optimisation
- PIM-4914: Fixed Quick export file name
- PIM-4458: Fix name display in pinbar for product edit pages

## BC breaks
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\MassEditActionController`, added `$gridNameRouteMapping` as the last argument.
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller`, added `$gridNameRouteMapping` as the last argument.

# 1.4.1 (2015-09-24)

## BC breaks
- Change constructor of `Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer`.
  Added `Liip\ImagineBundle\Imagine\Cache\CacheManager`, `Liip\ImagineBundle\Imagine\Data\DataManager` and `Liip\ImagineBundle\Imagine\Filter\FilterManager`

## Bug fixes
- PIM-4882: Fix pinbar issue (bump oro/platform version)
- PIM-4880: Fix PDF download for product with media
- PIM-4911: Fix product edit form string escaping
- PIM-4839: Fix the random skip of the carriage returns during an import

# 1.4.0 (2015-09-23)

## BC breaks
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\AssociationTypeRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\AttributeOptionRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\AttributeRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\CategoryRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\FamilyRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\GroupRemover`
- Removed `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\ProductRemover`
- Removed `Pim\Bundle\CatalogBundle\Event\AssociationTypeEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\AttributeEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\AttributeOptionEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\CategoryEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\FamilyEvents`
- Removed `Pim\Bundle\CatalogBundle\Event\GroupEvents`
- Removed event `pim_catalog.pre_remove.association_type` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.attribute` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.post_remove.attribute` use `akeneo.storage.post_remove` instead
- Removed event `pim_catalog.pre_remove.attribute_option` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.category` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.tree` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.family` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.group` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.pre_remove.product` use `akeneo.storage.pre_remove` instead
- Removed event `pim_catalog.post_remove.product` use `akeneo.storage.post_remove` instead
- Added function `isBackendTypeReferenceData` to the `Pim\Bundle\CatalogBundle\Model\AttributeInterface`

## Bug fixes
- PIM-4882: Cannot import products into a variant group if an axis is a reference data
- PIM-4917: Fix the issue with decimal storage for prices and number and add the related number comparator

# 1.4.0-RC1 (2015-09-04)

## Technical improvements

- Rename FileStorage classes and services: File (file information stored in database) => FileInfo, RawFile (physical file on the disk) => File
- Change namespace of Classification component and bundle from Pim to Akeneo

# 1.4.0-BETA3 (2015-09-02)

## BC breaks
- Change the constructor of `Pim\Bundle\UserBundle\Context\UserContext`. Takes `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface`, `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`, `Symfony\Component\HttpFoundation\RequestStack`, `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface` and a `$defaultLocale` string
- Remove deprecated `AbstractDoctrineController` parent to `Pim\Bundle\EnrichBundle\Controller\CategoryTreeControlle`. Now it extends `Symfony\Bundle\FrameworkBundle\Controller\Controller`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CategoryTreeController`, added `$rawConfiguration` as the last argument. Removed `Symfony\Component\HttpFoundation\Request`, `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface`, `Symfony\Component\Routing\RouterInterface`, `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`, `Symfony\Component\Form\FormFactoryInterface`, `Symfony\Component\Validator\Validator\ValidatorInterface`, `Symfony\Component\Translation\TranslatorInterface`, `Doctrine\Common\Persistence\ManagerRegistry` and `Pim\Bundle\CatalogBundle\Manager\CategoryManager`
- Rename service `pim_enrich.controller.category_tree` to `pim_enrich.controller.category_tree.product`
- Change constructor of `src/Pim/Bundle/EnrichBundle/Twig/CategoryExtension` to remove `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` and `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`. Added `Pim\Bundle\EnrichBundle\Doctrine\Counter\CategoryItemsCounterRegistryInterface`
- Add `$getChildrenTreeByParentId` to `getChildrenTreeByParentId` of `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\GroupColumnsConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\GroupColumnsConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator` to `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\SortersConfigurator`
- Move `Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractor` to `Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractor`
- Move `Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface` to `Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractorInterface`

# 1.4.0-BETA2 (2015-08-17)

## Bug fixes
- PIM-4443: Exporting a product with an attribute with a numeric code gives an error, full numeric codes for entities are now forbidden except for products

## BC breaks
- Move `Pim\Bundle\ImportExportBundle\Factory\JobInstanceFactory` to `Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory`
- Media classes `Pim\Bundle\CatalogBundle\Model\ProductMedia`, `Pim\Bundle\CatalogBundle\Model\AbstractProductMedia` and `Pim\Bundle\CatalogBundle\Model\ProductMediaInterface` have been removed
- Media denormalizers `Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\MediaDenormalizer`, `Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\MediaDenormalizer` have been removed
- Media normalizers `Pim\Bundle\TransformBundle\Normalizer\Structured\MediaNormalizer`, `Pim\Bundle\TransformBundle\Normalizer\Flat\MediaNormalizer` have been removed
- Media related classes `Pim\Component\Catalog\Comparator\Attribute\MediaComparator` and `Pim\Bundle\EnrichBundle\Controller\MediaController` have been removed
- Class `Pim\Bundle\BaseConnectorBundle\Writer\File\ProductWriter` has been removed
- Change constructor of `Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter` to remove `Pim\Bundle\CatalogBundle\Manager\MediaManager`, `Pim\Bundle\CatalogBundle\Factory\MediaFactory` and the upload directory parameter and to add `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface`, `Akeneo\Component\FileStorage\File\FileStorerInterface` and `League\Flysystem\MountManager`
- Change constructor of `Pim\Component\Catalog\Updater\Setter\MediaAttributeCopier` to remove `Pim\Bundle\CatalogBundle\Manager\MediaManager` and `Pim\Bundle\CatalogBundle\Factory\MediaFactory` and to add `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface`, `Akeneo\Component\FileStorage\File\FileStorerInterface` and `League\Flysystem\MountManager`
- Change constructor of `Pim\Bundle\TransformBundle\Transformer\Property\MediaTransformer` to remove media class parameter and to add `Akeneo\Component\FileStorage\File\FileStorerInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor` to remove the upload directory parameter and to add the media attributes types
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Writer\File\CsvProductWriter` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Pim\Component\Connector\Writer\File\FileExporterInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Writer\File\CsvVariantGroupWriter` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Pim\Component\Connector\Writer\File\FileExporterInterface`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Archiver\ArchivableFileWriterArchiver` to remove the archive directory parameter
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver` to remove the archive directory parameter
- Change method `createZip` of `Pim\Bundle\BaseConnectorBundle\Filesystem\ZipFilesystemFactory` to return a `League\Flysystem\Filesystem`
- Change method `getArchive` of `Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface` to return a `resource`
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Akeneo\Component\FileStorage\File\FileStorerInterface`
- Remove method `generateFilenamePrefix` of `Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` to replace `Pim\Bundle\CatalogBundle\Manager\MediaManager` by `Akeneo\Component\FileStorage\File\FileStorerInterface` and to remove the upload directory parameter
- Change constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\AttributeSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ChannelSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\FamilySaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\GroupSaver` to add event dispatcher `Symfony\Component\EventDispatcher\EventDispatcherInterface`
- `updateAction` has been removed from the `Pim\Bundle\EnrichBundle\Controller\ProductController`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` to remove product remover `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\ProductRemover`
- Moved the `removeAction` from `Pim\Bundle\EnrichBundle\Controller\ProductController` to `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController`

# 1.4.0-BETA1 (2015-07-31)

## Features

## Technical improvements
- Revamp the Readers, Processors and Writers to import data, make them more simple and re-useable
- Use DEFERRED_EXPLICIT as Doctrine changeTrackingPolicy (for all models)
- Continue to group persist()/flush() to the dedicated layer (SaverInterface) to avoid to have them everywhere in the stack
- Category filter is separated from other datagrid filters for performance concerns (parallel ajax requests)
- Use MySQL as a non blocking session storage
- Handle Doctrine mapping overrides smoothly (no more need to copy/paste the full mapping of an entity or a document)
- Product edit form revamp to handle lot of attributes, locales and channels per product
- Re-work the import/export engine by introducing a new Connector (component+bundle), the old one is deprecated but still useable
- Re-work the installer to use the new import engine (remove the yml format for fixtures)
- Remove the yml category and association fixtures
- Migrate to Symfony 2.7

## Bug fixes
- PIM-3874: clicking a category gives an error with only "list categories" permission
- PIM-3771: Create version when modifying variant group attribute
- PIM-2743: keep page per view on datagrids
- PIM-3758: Hide the category tree on products grid if user do not have the right to list categories
- PIM-3929: Categories with circular references are skipped in processor during import
- PIM-4024: Fix for metric and price denormalizer
- PIM-4314: Added missing translation keys
- PIM-4112: Not translated Error message when wrong format import
- PMI-4032: Fix wrong error message when deleting used attribute option by a published product

## BC breaks
- Change the constructor of `Pim\Bundle\UserBundle\Context\UserContext`, `Pim\Bundle\UserBundle\Form\Type\UserType`, `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, `Pim\Bundle\EnrichBundle\Controller\JobExecutionController`, `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`, `Pim\Bundle\EnrichBundle\Controller\VariantGroupController` and `Pim\Bundle\EnrichBundle\EventListener\UserContextListener`. Replace `Symfony\Component\Security\Core\SecurityContext` by `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
- Change the constructor of `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, added `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
- Change interface `Symfony\Component\Validator\ValidatorInterface` to `Symfony\Component\Validator\Validator\ValidatorInterface`
- Change interface `Symfony\Component\OptionsResolver\OptionsResolverInterface` to `Symfony\Component\OptionsResolver\OptionsResolver`
- Change interface `Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase` to `Symfony\Component\Form\Test\TypeTestCase`
- Change interface `Symfony\Component\Form\Extension\Core\View\ChoiceView` to `Symfony\Component\Form\ChoiceList\View\ChoiceView`
- Change interface `Symfony\Component\Validator\MetadataFactoryInterface` to `Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface`
- Change interface `Symfony\Component\Validator\ExecutionContextInterface` to `Symfony\Component\Validator\Context\ExecutionContextInterface`
- Remove bundle StofDoctrineExtensionsBundle
- Symfony events `FormEvents::POST_BIND` and `FormEvents::BIND` have been replaced by `FormEvents::POST_SUBMIT` and `FormEvents::SUBMIT` in `Pim\Bundle\TranslationBundle\Form\Subscriber`
- Rename methods `bind()` and `postBind()` by `submit()` and `postSubmit()` in `Pim\Bundle\TranslationBundle\Form\Subscriber`
- Rename method `setDefaultOptions()` to `configureOptions()` in all form types
- Service `pim_catalog.validator.product` calls now `Symfony\Component\Validator\Validator\RecursiveValidator`, take the `pim_catalog.validator.context.factory` service as the first argument and remove the fourth and fifth argument
- Add `Symfony\Component\HttpFoundation\RequestStack` as the fifth argument in `Pim\Bundle\UserBundle\Context\UserContext`, `$defaultLocale` become the sixth argument and `Symfony\Component\HttpFoundation\Request` is no longer called
- Remove connections `report_source` and `report_target` from dbal in `app/config/config.yml`
- `normalize` method of `Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValueNormalizer` returns an array with a "data" key instead of "value" key
- `Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\VariantGroupWriter` and `Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\VariantGroupProcessor` are deprecated
- Change the constructor of `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`, removed `Pim\Bundle\VersioningBundle\Manager\VersionManager`
- Rename method `onKernelRequest` to `findUsername` on `Pim\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber`
- Change the constructor of `Pim\Bundle\VersioningBundle\Manager\VersionManager`, added `Symfony\Component\EventDispatcher\EventDispatcherInterface` as the last argument
- Change the constructor of `Pim/Bundle/TransformBundle/Denormalizer/Structured/ProductValuesDenormalizer`, removed `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`, added `Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry` as the second argument and `pim_catalog.entity.attribute.class` as the last argument
- Change the constructor of `Pim/Bundle/TransformBundle/Normalizer/Structured/GroupNormalizer`, added `Symfony\Component\Serializer\Normalizer\DenormalizerInterface` as the last argument
- Change the constructor of `Pim/Bundle/CatalogBundle/Doctrine/Common/Remover/AttributeRemover` to accept `Pim/Bundle/CatalogBundle/Builder/ProductTemplateBuilder` as the fourth argument and accept `Pim/Bundle/CatalogBundle/Entity/Repository/ProductTemplateRepository` as the fifth argument
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/CompletenessRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductCategoryRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductMassActionRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/MongoDBODM/{ → Repository}/ProductRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/AssociationRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AssociationTypeRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeGroupRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeOptionRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/AttributeRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/CategoryRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/ChannelRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/CompletenessRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/CurrencyRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/FamilyRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/GroupRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/GroupTypeRepository.php
- Move Pim/Bundle/CatalogBundle/{Entity → Doctrine/ORM}/Repository/LocaleRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductCategoryRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductMassActionRepository.php
- Move Pim/Bundle/CatalogBundle/Doctrine/ORM/{ → Repository}/ProductRepository.php
- Remove Pim/Bundle/CatalogBundle/ProductManager::createProductValue, saveProduct, saveAllProducts
- Add AttributeRepositoryInterface, FamilyRepositoryInterface, AssociationTypeRepositoryInterface and EventDispatcherInterface as arguments of the constructor of Pim/Bundle/CatalogBundle/Builder/ProductBuilder
- Remove ProductManager and add AttributeRepositoryInterface in arguments of the constructor of Pim/Bundle/CatalogBundle/Factory/FamilyFactory
- Remove ProductManager, add ProductBuilderInterface, ProductRepositoryInterface, $productClass and $productValueClass in arguments of the constructor of Pim/Bundle/TransformBundle/Transformer/ProductTransformer
- Remove ProductManager, add AttributeRepositoryInterface in arguments of the constructor of Pim/Bundle/CatalogBundle/Validator/Constraints/SingleIdentifierAttributeValidator
- Move Pim/Bundle/CatalogBundle/Updater/Setter/AbstractValueSetter.php → Pim/Component/Catalog/Updater/Setter/AbstractAttributeSetter
- Remove AttributeRepositoryInterface argument from constructor of Pim/Component/Catalog/Updater/Setter/SetterRegistryInterface, remove method get(
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/BooleanValueSetter -> Pim/Component/Catalog/Updater/Setter/BooleanAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/DateValueSetter -> Pim/Component/Catalog/Updater/Setter/DateAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/MediaValueSetter -> Pim/Component/Catalog/Updater/Setter/MediaAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/MetricValueSetter -> Pim/Component/Catalog/Updater/Setter/MetricAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/MultiSelectValueSetter -> Pim/Component/Catalog/Updater/Setter/MultiSelectAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/NumberValueSetter -> Pim/Component/Catalog/Updater/Setter/NumberAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/PriceCollectionValueSetter -> Pim/Component/Catalog/Updater/Setter/PriceCollectionAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/SimpleSelectValueSetter -> Pim/Component/Catalog/Updater/Setter/SimpleSelectAttributeSetter
- Rename Pim/Bundle/CatalogBundle/Updater/Setter/TextValueSetter -> Pim/Component/Catalog/Updater/Setter/TextAttributeSetter
- Remove setValue and supports from Pim/Component/Catalog/Updater/Setter/SetterInterface
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/CopierInterface -> Pim/Component/Catalog/Updater/Copier/AttributeCopierInterface
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/AbstractValueCopier -> src/Pim/Component/Catalog/Updater/Copier/AbstractAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/BaseValueCopier -> src/Pim/Component/Catalog/Updater/Copier/BaseAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/MediaValueCopier -> src/Pim/Component/Catalog/Updater/Copier/MediaAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/MetricValueCopier -> src/Pim/Component/Catalog/Updater/Copier/MetricAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/MultiSelectValueCopier -> src/Pim/Component/Catalog/Updater/Copier/MultiSelectAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/PriceCollectionValueCopier -> src/Pim/Component/Catalog/Updater/Copier/PriceCollectionAttributeCopier
- Rename Pim/Bundle/CatalogBundle/Updater/Copier/SimpleSelectValueCopier -> src/Pim/Component/Catalog/Updater/Copier/SimpleSelectAttributeCopier
- Remove MediaManager from constructor of Pim\Bundle\CatalogBundle\Manager\ProductManager
- Remove deprecated handleMedia() and handleAllMedia() from Pim\Bundle\CatalogBundle\Manager\ProductManager
- Replace argument ProductManager by MediaManager in constructor of Pim\Bundle\BaseConnectorBundle\Writer\DirectToDB\MongoDB\ProductWriter
- Remove deprecated Pim/Bundle/BaseConnectorBundle/Reader/ORM/CursorReader
- Remove deprecated Pim/Bundle/BaseConnectorBundle/Reader/Doctrine/BulkProductReader and Pim/Bundle/BaseConnectorBundle/Reader/Doctrine/ObsoleteProductReader
- Remove deprecated Pim/Bundle/CatalogBundle/Repository/ReferableEntityRepositoryInterface and Pim/Bundle/CatalogBundle/Doctrine/ReferableEntityRepository
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/AssociationTypeManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/AttributeManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/CategoryManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/FamilyManager
- Remove deprecated remove() from Pim/Bundle/CatalogBundle/Manager/GroupManager
- Change arguments of Pim/Bundle/EnrichBundle/Controller/AssociationController to use AssociationTypeRepositoryInterface, ProductRepositoryInterface, ProductBuilderInterface, EngineInterface
- Remove arguments ChannelRepositoryInterface, LocaleRepositoryInterface, add argument AttributeValuesResolver in Pim/Bundle/CatalogBundle/Builder/ProductBuilder constructor
- Remove arguments DenormalizerInterface, ValidatorInterface, ObjectDetacherInterface, $class from the constructor of Pim/Bundle/BaseConnectorBundle/Processor/Denormalization/AbstractProcessor
- Add methods `getReferenceDataName` and `setReferenceDataName` to Pim\Bundle\CatalogBundle\Model\AttributeInterface.
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\MassEditActionController`, removed `Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry`, `Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher`, `$massEditLimit`, added `Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface`, `Pim\Bundle\ConnectorBundle\JobLauncher\SimpleJobLauncher`, `Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository`, `Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry`, `Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface`, `Pim\Bundle\EnrichBundle\MassEditAction\MassEditFormResolver`
- Remove `Pim\Bundle\EnrichBundle\Form\Subscriber\MassEditAction\AddSelectedOperationSubscriber`
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToGroupsType`, added `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` as first argument
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\AddToVariantGroupType`, added `Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface` as first argument and `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` as second argument
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType`, added `Pim\Bundle\CatalogBundle\Manager\CategoryManager` as second argument
- Rename `Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType\MassEditOperatorType` -> `Pim\Bundle\EnrichBundle\Form\Type\MassEditChooseActionType`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditAction`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, removed `Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface` and `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups`, updated method `setGroups` to accept `Doctrine\Common\Collections\ArrayCollection`, removed method `getWarningMessages`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Remove constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup`, removed method `setObjectsToMassEdit`, `perform`, `getWarningMessages`, `getValidVariantGroups`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily` now imlements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily`, removed method `affectsCompleteness`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Remove constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify`, removed method `getTrees`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- Change constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, removed `Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface`, `Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager`, `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface`, added `Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface`, `Pim\Bundle\CatalogBundle\Manager\MediaManager`, `Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager` and `$uploadDir`
- Change `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes`, removed method `affectsCompleteness`, `perform`, `getCommonAttributes`, `getWarningMessages`
- Change interface `Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface`, removed method `getFormType`, `getFormOptions`, `initialize`, `perform`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation`
- `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements` now implements `Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditOperation` instead of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\FamilyMassEditOperation`
- `Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\ProductWriter` now implements `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` instead of `Pim\Bundle\TransformBundle\Cache\CacheClearer`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\ProductMassEditOperator`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\FamilyMassEditOperation`
- Completely refactor the `\Pim\Bundle\DataGridBundle\Controller\ProductExportController`. It implement nothing instead of `\Pim\Bundle\DataGridBundle\Controller\ExportController`. Now it launched job in backend end.
- Remove `Pim\Bundle\EnrichBundle\DependencyInjection\Compiler\RegisterMassEditOperatorsPass`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\FamilyMassEditOperator`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operator\MassEditOperatorInterface`
- Remove `Pim\Bundle\EnrichBundle\MassEditAction\OperatorRegistry`
- Move `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\CategoryRepository` → `Akeneo\Bundle\ClassificationBundle\Doctrine\ORM\Repository\CategoryRepository`
- Move `Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface` → `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Remove Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface replaced by Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface
- Remove Pim\Bundle\TransformBundle\Normalizer\Filter\FilterableNormalizerInterface
- Remove `Pim\Bundle\EnrichBundle\Controller\ProductAttributeController`
- ProductTemplateUpdater now takes ProductPropertyUpdaterInterface as argument and not anymore ProductUpdaterInterface
- Remove fixtures_product_yml and fixtures_association_yml from the InstallerBundle, csv format is now mandatory for products
- ProductUpdater takes ValidatorInterface as second argument
- Rename `Pim\Bundle\TransformBundle\Builder\FieldBuilder` to `Pim\Component\Connector\ArrayConverter\Flat\AttributeColumnInfoExtractor`
- Method `createAttribute` of Pim/Bundle/CatalogBundle/Manager/AttributeManager.php is now deprecated use `AttributeFactory::createAttribute` instead
- Constructor of `Pim\Bundle\EnrichBundle\Controller\ChannelController` now takes a BulkSaver as last argument (to save locales)
- Constructor of `Pim\Bundle\CatalogBundle\Manager\AttributeManager` has been changed
- Constructor of `Pim\Bundle\CatalogBundle\Manager\AttributeGroupManager` has been changed
- Constructor of `Pim\Bundle\CommentBundle\Manager\CommentManager` has been changed
- Constructor of `Pim\Bundle\DatagridBundle\Manager\DatagridViewManager` has been changed
- Constructor of `Pim\Bundle\EnrichBundle\Manager\SequentialEditManager` has been changed
- Depreciate and change constructor of `Pim\Bundle\TransformBundle\Builder\FieldNameBuilder`
- Replace the argument ProductManager by ProductRepositoryInterface in the constructor of `Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueVariantAxisValidator`
- Add an argument BulkSaverInterface in the constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Remover\CategoryRemover`
- Constructor of `Pim\Bundle/CatalogBundle/Manager/CompletenessManager` : removed dependency on `Symfony\Component\Validator\ValidatorInterface`
- Constructor of `Pim\Bundle/CatalogBundle/Manager/CompletenessManager` : added dependency on `Pim\Component\Catalog\Completeness\Checker\ChainedProductValueCompleteChecker`
- Change constructor of `Pim\Bundle\CatalogBundle\Manager\CategoryManager`, added `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` and `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` as third and fourth argument.
- Change `Pim\Bundle\CatalogBundle\Manager\CategoryManager`, updated method `getEntityRepository` to return return a `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\CategoryTreeController`, added `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` and `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface` as last arguments
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController`, added `Pim\Bundle\CatalogBundle\Factory\CategoryFactory` as last argument
- Move `Pim\Bundle\FilterBundle\Filter\Product\CategoryFilter` to `Pim\Bundle\FilterBundle\Filter\CategoryFilter`
- Change constructor of `Pim\Bundle\FilterBundle\Filter\CategoryFilter`, last argument is now a `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter`, second argument is now a `Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface`
- Remove the option 'flush_only_object' from `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver`, `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseSaver`, `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver\BaseRemover`
- Add an argument `Pim/Bundle/VersioningBundle/Factory/VersionFactory` in the constructor of `Pim/Bundle/VersioningBundle/Builder/VersionBuilder`
- Add an argument `Symfony\Component\EventDispatcher\EventDispatcher` in the constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover`
- Change constructor of `Pim\Bundle\BaseConnectorBundle\Reader\ORM\CategoryReader`, argument is now a `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Change constructor of `Pim\Bundle\UserBundle\Context\UserContext`, replace `Pim\Bundle\CatalogBundle\Manager\LocaleManager` and `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface` and `Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface`, add `Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface`
- Constructor of `Pim\Bundle\CatalogBundle\Manager\CategoryManager` has been changed

