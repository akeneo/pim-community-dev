# 1.2.14 (2014-12-03)

## Bug fixes
- PIM-3443: Fix prices not exported in quick export
- PIM-3446: Fix import export history with large amount of errors

# 1.2.13 (2014-11-26)

## Bug fixes
- PIM-3406: Fix boolean filter on Mongo implementation
- PIM-3430: Fix doctrine issue on prices when skip an item during the product import
- PIM-3358: Fix sprintf issue in an exception which prevents doctrine writer to deal with anything else than an object
- PIM-3326: Fix mongo filters with multiples values and empty on MongoDB
- PIM-3426: Fix common attributes edition on multi selects
- PIM-3434: Fix bug in product media manager when file does not exist on the filesystem
- PIM-3436: Fix WYSIWYG field on product edit form (load them asynchronously)
- PIM-3372: Add an error message when the locale is disabled during product import
- PIM-3370: Add an error message when the channel doesnt exist during product import
- PIM-3374: Add an error message when a channel is provided for a global attribute
- PIM-3375: Add an error message when a locale is provided for a global attribute
- PIM-3376: Add an error message when a channel and a locale are provided for a global attribute
- PIM-3393: Don't show the update view button for non-owners

# 1.2.12 (2014-11-13)

## Bug fixes
- PIM-3298: Fix issue with locale specific property of an attribute when edit and mass edit
- PIM-3229: Fix values for simple and multi select attributes with missing translations not being displayed in the grid
- PIM-3309: Fix check on product value uniqueness
- PIM-3288: Fix memory leak on product import (avoid to hydrate all products of a category when we add a category to a product)
- PIM-3354: Fix parameter alias in ORM ProductCategoryRepository

# 1.2.11 (2014-10-31)

## Bug fixes
- PIM-3308: Fix regression on unclassified filter
- PIM-3311: Fix creation of products with missing identifier during imports
- PIM-3312: Fix CSV import of product values with invalid channel, locale or currency

# 1.2.10 (2014-10-24)

## Bug fixes
- PIM-3221: Fix the possibility to update attributes on variant groups during import
- PIM-3283: Fix issue on the password reset
- PIM-3209: Fix issue on the extension validation during import
- PIM-3234: Fix performance issue on category filter

# 1.2.9 (2014-10-17)

## Bug fixes
- PIM-3254: Fix issue with inactive locales in exports
- PIM-3217: Fix missing filter groups in grid filter selector when two attribute groups have the same sort orders
- PIM-3281: Fix mass edit issue on localizable values, it uses user locale instead of selected locale
- PIM-3248: Fix completeness not being correctly calculated after removing a required attribute from a family
- PIM-3279: Fix performance issue with big group sets
- PIM-3266: Fix the flush of skipped items during an import that uses the `Pim\Bundle\BaseConnectorBundle\Processor\TransformerProcessor`. All your custom processors that uses the `TransformmerProcessor` should now inject the `Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry` to fix this issue too.
- PIM-3282: Fix the grid filters that can be set as json in the request

## BC breaks
- Two new arguments have been added to Pim\Bundle\FilterBundle\Filter\Product\GroupsFilter: `userContext` and `groupClass`

# 1.2.8 (2014-10-10)

## Bug fixes
- Fix memory leak in CSV quick export
- Fix memory leak when product with medias are exported in CSV
- Cannot display correctly all variant groups on grid

## Improvements
- avoid hydrating duplicate categories when applying category filter in product grid

# 1.2.7 (2014-10-01)

## Bug fixes
- Fix no warning message when leaving a product form after a submit with errors
- Stabilize composer.json (minimum-stability: stable) and fix monolog version issue

# 1.2.6 (2014-09-26)

## Bug fixes
- Fix installer fail on requirements when you change the archive and uploads folder
- Fix display of multi-byte characters in long form labels that are truncated
- Incorrect date display between export/import widget and job execution page and job history
- Fix archiver bug with yml imports
- Fix missing product versioning data when a category, attribute or attribute option linked to a product is removed

## BC breaks
- Added supports method in Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface
- Two new methods have been added to Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface: `findAllWithAttribute` and `findAllWithAttributeOption`
- Constructor of Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeOptionUpdateGuesser has been changed

## Improvements
- Add images in icecat_demo_dev installer fixtures
- Add sorter to the grid state

# 1.2.5 (2014-09-19)

## Bug fixes
- File that contains non UTF-8 characters can not be imported anymore
- Mimetype check on file import has been removed
- Incorrect written number after csv export

## Improvements
- Fixtures stop if warnings are encountered
- Errors and warnings for fixtures are displayed

# 1.2.4 (2014-09-11)

## Bug fixes
- Fixed job profile controller doing a global flush after launching job execution

# 1.2.3 (2014-09-08)

## Bug fixes
- association fixtures

# 1.2.2 (2014-09-05)

## Improvements
- CacheClearer splits into two services, one for Product and one for other entities

## Bug fixes
- association import with MongoDB fixes

# 1.2.1 (2014-09-03)

## Bug fixes
- large memory leak fixed for non product import (association, product group, attribute, categories, etc...)
- new associations were created at each import

## BC breaks
- protected postWrite method not called anymore from BaseConnectorBundle\\Writer\\Doctrine\\Writer.
 If you need it, override the write method, call the parent and add your code after.
- constructor of Pim\Bundle\BaseConnectorBundle\Writer\Doctrine\Writer has changed
- Pim\Bundle\TransformBundle\Cache\ProductCacheClearer has been renamed Pim\Bundle\TransformBundle\Cache\CacheClearer

# 1.2.0 (2014-08-28)

## Improvements

## Bug fixes
- Fix a bug on entity `Pim/Bundle/CatalogBundle/Model/AbstractProduct`

# 1.2.0-RC4

## Improvements
- Java dependency has been removed
- Add locale fallback to en_US

## Bug fixes
- Sort exported categories by tree and order inside the tree
- Return to the family index page after cancelling family mass edit instead of product index
- Fixed an error when all families are edited without any applied filters
- Fixed a bug that allowed to mass edit only 10 families
- Fixed category order in the categories tab of products
- Incomplete archives no longer appear as downloadable in the export execution details page
- Fixed Cascade delete on associations for MongoDB impl
- Fixed a bug on normalization of decimal attributes for MongoDB impl
- Fixed the 'Is associated' filter in the product association grids
- Fixed a bug where special characters were not well handled in product grid filter
- Fixed unique value validation for date attributes during import
- Fixed apply filter on channel tree on MongoDB implementation
- Fixed a bug on ProductCsvWriter
- Fixed a bug that causes product associations to be stored twice in MongoDB implementation

## BC breaks
- Replace ACLs `pim_enrich_family_add_atribute` and `pim_enrich_family_remove_atribute` with `pim_enrich_family_edit_attributes`. This ACL also enforces rights to edit attribute requirements.
- Changed JobExecutionArchivist to archive files generated by export before it is marked as completed
- JS and CSS are not minified anymore. We advise to use server side compression for bandwidth savings.

# 1.2.0-RC3

## Improvements
- Killed export process are now detected and displayed as failed
- CsvWriter can write files for any type of entity

## Bug fixes
- Fixed Mass edit on a never fulfilled price attribute
- Fix TinyMCE WYSIWYG editor generating 'fake' history due to html reformatting
- Fixed flat product normalizer and filtered values (with many filters)
- Make sure that the file path of export profiles is writable before allowing to execute
- Fixed bug with scopable boolean value not being saved
- Use `pim_number` form type to replace the use of `number` and fix issue with javascript validation on numbers with different formats

## BC breaks
- Remove `task` option from install command
- JobExecutionController now require the Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager.
- InvalidItemsCsvArchiver is not injected in the constructors of ProductCsvReader and ProductReader
- CsvProductWriter should be used instead of CsvWriter for products
- Remove `pim_serializer.normalizer.get_set_method` defined as fallback normalizer

# 1.2.0-RC2

## Improvements
- Create a metric factory
- Improve UI for defining role permissions
- Throw exception on install command if fixture directory not found
- Setup `pim_catalog.storage_driver` in `pim_parameters.yml` instead of `config.yml`
- Load PIM configuration via the import of the file `pim.yml` instead of a preprend configuration
- Externalize non local PIM parameters in `pim_parameters.yml`
- Replace buttons by icons to manage datagrid views
- Add post create event on enrich part when an attribute group is created

## Bug fixes
- The message 'there are unsaved changes' is missing in the Role edit form
- Display a file attribute attribute as column in product grid displays Array
- History tab crashes when product imported without real time versioning
- Creating an attribute with the code "id" should be forbidden
- Switch not well displayed on other locales than en_US
- Associations are now well saved on product import

## BC breaks
- Remove backendStorage property on attribute entities
- Inject MetricFactory in `Pim\Bundle\CatalogBundle\AttributeType\MetricType`, `Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttribute` and `Pim\Bundle\TransformBundle\Transformer\Property\MetricTransformer` instead of metric entity class parameter
- MongoDB: Media are now part of the product as embedded document and not in an external collection. A migration script is provided. See the UPGRADE file.
- Change constructor of `Pim\Bundle\EnrichBundle\Form\Type\FamilyType` to add `DisableFamilyFieldsSubscriber` as third argument and `AddAttributeAsLabelSubscriber` as fourth argument
- Rename pim_catalog.datasource.smart and pim_catalog.datasource.product respectively by pim_datagrid.datasource.smart and pim_datagrid.datasource.product
- Add method setMassActionRepository and remove the MassActionRepositoryInterface from constructor
- Introduce a dedicated pim_webservice.serializer to handle REST API
- Rename ACL `pim_enrich_family_edit` to `pim_enrich_family_edit_properties`. This ACL now only check the access to the properties tab.
- Rename ACL `pim_enrich_product_edit` to `pim_enrich_product_edit_attributes`. This ACL now only check the access to the attributes tab.
- Added ProductCacheClearer to share cache clearing between product writers

# 1.2.0-RC1

## Features
- Add an option to automatically sort the choices of simple and multi select attributes
- Add a mass family edition operation to allow adding or changing attribute requirements on many families at once
- Allow filtering by empty values for attributes (text, textarea, number, date, simple and multiselect, prices and metrics) and for family property
- Add an option to filter products by a list of identifier values
- Don't allow editing the default datagrid view
- Add a enable/disable row action in product grid

## Improvements
- Group datagrid filters by attribute groups
- Ease the adding of new filters and sorters in ProductQueryBuilder
- All grids can now benefit from the multistep mass edition wizard (this was reserved to the the product grid before)
- Ease the adding of subscribers in ProductEditType, JobInstanceType and AttributeGroupType with addEventSubscriber methods
- Introduce a ProductValueFormFactory which dispatch a EnrichEvents::CREATE_PRODUCT_VALUE_FORM to ease the product value form customization
- MongoDB completeness calculation performances
- Introduce Abstract models for Association, Media, Metric, Price, Completeness to ease the overriding/re-using of theses classes
- Allow to override of a repository avoiding to redefine the entity mapping
- Introduce a datagrid choice filter that loads attribute option choices based on the search query to enhance performance with a large number of attribute options
- Apply "Remove product" permission to hide mass delete and delete row action
- Change "launch" button by "view" on job profile datagrids
- Create a `JobInstanceRepository`
- Automatic creation and purge of indexes for MongoDB
- Dispatch event before rendering the product edit template
- Fixed asymetric enable product button
- Remove qb definition from job profile grid configs
- Create repositories for JobInstance and JobExecution
- Create manager for JobInstance
- Clean LastOperationsWidget architecture
- New readers for export improve memory usage loading small batches of products instead of all products in same time
- Update BatchBundle to 0.1.6 in order to get updated summary information during the execution of the process (and not only at the end)
- Allow values 'true', 'false', 'yes' and 'no' to be converted into boolean during import
- Create a job instance factory to create job instances
- Allow to add hidden row actions in grids
- Make optional the generation of missing completenesses in product reader
- Update install to be able to define email address/name used for system emailing
- Update BatchBundle version to get a better support of exceptions in logs and provide the new command akeneo:batch:list-jobs
- Faster MongoDB product writer (around 10x times faster than current one)
- Dispatch events on show/edit/execute/remove job profile actions
- Dispatch events on view/download job execution actions
- Allow to install custom user roles and groups from installer fixtures
- Display the code of import/export profiles on the edit and show views
- Related entities' edition and deletion doesn't reload all the products' normalized data
- Inject event dispatcher inside AbstractController
- Csv reader and Yaml readers are now reseted between each steps
- Dispatch events when removing some entities
- Add method remove in Category, Group, Attribute, Association type and family managers.
- Call manager's method remove from these entity controllers
- Remove the count of products by category in the context of the management of the categories (perf)
- Define attribute type classes as parameters
- Products on which mass edit operation is not performed are also ignored from operation finalize method
- Create specific serializer service for versioning

## Bug fixes
- Replaced usage of Symfony process to launch background job with a simple exec, more reliable on a heavily loaded environment
- Added missing translation keys for "manage filters", "all", "records", etc
- Images import from fixtures now works
- Fixed versions not being properly generated when real-time versioning is disabled (in imports/exports)
- Deleted completeness when a locale of a channel is deleted
- Displayed flags in the completenesses grid
- Fixed a memory leak on product import when using MongoDB
- Fixed a bug with image upload on product with a "\" or "/" in their sku
- Fixed a bug that silently failed when uploading file that does not comply with server configuration
- Fixed a bug when display image thumbnail in the product grid with MongoDB support
- Fixed a bug with timestampable listener which doesn't change the updated date of a product
- Fixed a bug with numeric validation and decimal allowed property (number, metric, price attribute types)
- Attribute Options code validation now more precise on uniqueness (equality instead of similarity)
- Fixed a bug with repository resolver on ODM implementation
- Fixed a bug on mass edit when we use a completeness filter to select products
- Removed the import CSV mimetype validation which is unreliable
- Product completeness in MongoDB is not lost anymore in the grid
- Upload on a job with a custom step (non ItemStep) doesn't crash anymore
- Memory leak fixed in pim:version:refresh command
- Fixed a bug when try to remove the family of a product
- Wrong date conversion fixes on grid and form

## BC breaks
- Remove FlexibleEntityBundle
- Remove CategoryWriter and use the generic doctrine writer instead
- Remove entity argument from FiltersConfigurator constructor
- Rely on CatalogBundle/Version and not anymore on CatalogBundle/PimCatalogBundle to get the current version of the PIM
- The Pim\Bundle\EnrichBundle\MassEditAction namespace has been renamed to Pim\Bundle\EnrichBundle\MassEditAction\Operation
- Mass edit operator has been moved to an Operator sub-namespace
- Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditActionInterface has been renamed Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface
- Changed the HydratorInterface::hydrate() method signature
- Avoid to store null values in Product::normalizedData (MongoDB support)
- Remove redundant 'getActiveCodeChoices' method in CurrencyManager (use CurrencyManager::getActiveCodes())
- Remove AbstractAttributeType::buildValueFormType, change visibility of prepareValueFormName, prepareValueFormAlias, prepareValueFormOptions, prepareValueFormConstraints, prepareValueFormData to public
- Remove `MetricBaseValuesSubscriber` and create one for MongoDB and another one for ORM
- Create `OptionFilter`, `OptionsFilter` for ORM and MongoDB implementations
- InstallerBundle/LoaderInterface has been changed to pass ProductManager to manage media (loading images from fixtures)
- Refactor VersioningBundle - a lot of API changes, add MongoDB support.
- Remove the Doctrine registry dependency from `Pim\Bundle\CatalogBundle\Manager\CompletenessManager` and use only the family repository
- Remove the Doctrine registry dependency from `Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator` and use only the entity manager
- Add a new method `scheduleForChannelAndLocale` to `Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface`
- Add a dependency to the completeness manager on `Pim\Bundle\EnrichBundle\Form\Handler\ChannelHandler`
- Add a dependency to the channel repository on `Pim\Bundle\CatalogBundle\Manager\CompletenessManager`
- Remove deprecated ConfigureGroupProductGridListener and add parameter in method ConfiguratorInterface::configure(DatagridConfiguration $configuration)
- Category and CategoryRepository no longer extend AbstractSegment and SegmentRepository, previously inherited methods are now in these classes
- Change constructor of ProductExportController to remove CurrencyManager and AssociationTypeManager args
- Change constructor of `Pim\Bundle\EnrichBundle\Controller\ProductController` and `Pim\Bundle\ImportExportController\JobProfileController` (inject event dispatcher)
- Add parameters to load datagrids in job profiles index twig templates
- Remove WidgetRepository to replace it by `Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository`
- Inject `Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager` into LastOperationsWidget
- Remove injection of WidgetRepository from LastOperationsWidget
- Inject JobInstanceFactory inside `Pim\Bundle\ImportExportBundle\Controller\JobProfileController`
- Remove duplicate pim_catalog.entity.job_instance.class parameter, we must now use akeneo_batch.entity.job_instance.class
- Inject EventDispatcher inside AbstractController
- Add missing getEntity() method in product value interface
- Add methods inside CategoryInterface
- Inject `Symfony\Component\EventDispatcher\EventDispatcherInterface` inside Attribute, AssociationType, Category, Family and Group managers
- Inject `Pim\Bundle\CatalogBundle\Manager\FamilyManager` in `Pim\Bundle\EnrichBundle\Controller\FamilyController`
- Inject `Doctrine\Common\Persistence\ObjectManager` in `Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager`
- Inject `Doctrine\Common\Persistence\ObjectManager` in `Pim\Bundle\CatalogBundle\Manager\FamilyManager`
- Inject group and group types classes in `Pim\Bundle\CatalogBundle\Manager\GroupManager`
- Inject `Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager` in `Pim\Bundle\EnrichBundle\Controller\AssociationTypeController`
- Inject `Pim\Bundle\CatalogBundle\Manager\FamilyManager` in `Pim\Bundle\EnrichBundle\Controller\FamilyController`
- Inject SecurityFacade inside `Pim\Bundle\EnrichBundle\Controller\CategoryController`
- Each dashboard widget has to define its full template, nothing is rendered automatically
- Delete `Pim\Bundle\DataGridBundle\Extension\Filter\MongoDBFilterExtension`, `Pim\Bundle\DataGridBundle\Extension\Filter\OrmFilterExtension`, `Pim\Bundle\DataGridBundle\Extension\Filter\ProductFilterExtension`
- Rename `Pim\Bundle\DataGridBundle\Extension\Filter\AbstractFilterExtension` to `Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension` which expects a `Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver\` as third argument for its constructor
- Rename constant `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddFilterTypesPass::FILTER_ORM_EXTENSION_ID` to `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddFilterTypesPass::FILTER_EXTENSION_ID`
- Delete `Pim\Bundle\DataGridBundle\Extension\Sorter\MongoDBSorterExtension`, `Pim\Bundle\DataGridBundle\Extension\Sorter\OrmSorterExtension`, `Pim\Bundle\DataGridBundle\Extension\Sorter\ProductSorterExtension`
- Rename `Pim\Bundle\DataGridBundle\Extension\Sorter\AbstractSorterExtension` to `Pim\Bundle\DataGridBundle\Extension\Sorter\SorterExtension`
- Rename constant `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddSortersPass::SORTER_ORM_EXTENSION_ID` to `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\AddSortersPass::SORTER_EXTENSION_ID`
- Delete service `pim_datagrid.extension.filter.mongodb_filter`
- Delete service `pim_datagrid.extension.filter.product_filter`
- Rename service `pim_datagrid.extension.filter.orm_filter` to `pim_datagrid.extension.filter`
- Delete service `pim_datagrid.extension.sorter.mongodb_sorter`
- Rename service `pim_datagrid.extension.sorter.orm_sorter` to `pim_datagrid.extension.sorter`
- Delete `Pim\Bundle\DataGridBundle\Extension\Pager\MongoDBPagerExtension`,`Pim\Bundle\DataGridBundle\Extension\Pager\OrmPagerExtension` and `Pim\Bundle\DataGridBundle\Extension\Pager\ProductPagerExtension`
- Rename `Pim\Bundle\DataGridBundle\Extension\Pagerr\AbstractPagerExtension` to `Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension` which expects a `PagerResolver` as first argument
- Delete service `pim_datagrid.extension.pager.mongodb_pager`
- Delete service `pim_datagrid.extension.pager.product_pager`
- Rename service `pim_datagrid.extension.pager.orm_pager` to `pim_datagrid.extension.pager`
- Replace `Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource` by `Pim\Bundle\DataGridBundle\Datasource\Datasource`
- Replace service `pim_datagrid.datasource.orm` by `pim_datagrid.datasource.default`
- Delete `Pim\Bundle\DataGridBundle\Datasource\MongoDB\MongoDBDatasource`
- Delete service `pim_datagrid.datasource.mongodb`
- Remove the flush parameter from Pim\Bundle\CatalogBundle\Doctrine\MongoDB\CompletenessGenerator::generateMissingForProduct(), as it was not used properly anymore (completeness are directly pushed to MongoDB without using ODM)
- Rename countForAttribute to countVariantGroupAxis in GroupRepository
- Remove locale-specific rights
- Upgraded to 0.2.* version of akeneo/batch-bundle
- Rename `Pim\Bundle\TransformBundle\DependencyInjection\Compiler\ReplacePimSerializerArgumentsPass` by `Pim\Bundle\Transform\DependencyInjection\Compiler\SerializerPass` and change construct parameters
- AddVersionListener and VersionBuilder use new `pim_versioning.serializer` service
- In InGroupFilter and IsAssociatedFilter constructors, replace the RequestParameters argument by a RequestParametersExtractorInterface
- Change constructor of `Pim\Bundle\DataGridBundle\Controller\ProductExportController` to inject the product repository `Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface` as fourth argument
- Rename Pim\CatalogBundle\Model\Media to Pim\CatalogBundle\Model\ProductMedia to reflect the coupling between product media and product value and allow the future introduction of generic media
- Add a $metricClass argument in MetricTransformer constructor
- Add a $mediaClass argument in MediaTransformer constructor
- Add a $metricClass argument in MetricType constructor
- Change the arguments of ProductBuilder to pass classes (product, value, price) as an array
- Change the arguments of EditCommonAttributes to pass classes (metric, media, price) as an array
- Remove not used parameter `pim_import_export.entity.export.class`
- Remove file `Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler\ResolveDoctrineOrmTargetEntitiesPass`
- Replace the filter config parent_type by ftype
- Rename CatalogBundle, VersioningBundle, UserBundle listeners to subscribers
- Change constructor of `Pim\Bundle\DataGridBundle\Manager\DatagridViewManager` to inject the datagrid view repository as first argument (instead of the manager)
- Rename service `pim_catalog.validator.attribute_constraint_guesser` by `pim_catalog.validator.constraint_guesser.chained_attribute`

# 1.1.0 - "Rabbit Punch" (2014-04-16)

## Features
- Implement creating, updating, applying and removing datagrid views
- MongoDB storage support

## Improvements
- Allow to add many quick export on datagrids
- Optimize products mass deletion
- Improve get product REST API
- Improve entity history context display for entities updated during import jobs
- Add a 'properties' field to the Attribute entity to allow easily adding new attribute type dependent properties
- Introduced custom ODM types to map document to one or many entities
- Define specific route and configuration for datagrid quick exports
- Add a parameter to ProductManager::save() and ProductManager::saveAll() to allow saving products without completeness recalculation
- Dispatch event pre/post handler for each mass action
- Enhance the error message displayed when a related entity doesn't exist during an import (for instance we import products and a family doesn't exist)
- Default product datagrid sorting is done descending on updated property

## Bug fixes
- Fixed the verbose option always used in the install command
- Fixed issue on attribute option removal
- Fixed identifier is required attribute
- Fixed get common attributes with common values
- Fixed completeness not removed from changed family
- Fixed Product ORM mapping: activated orphanRemoval of values
- Fixed family import fixtures, we now throw an exception if attribute used as requirement not exists
- Fixed the CSV import of attribute options which can fail due to missing columns when options are not 100% translated
- Fixed the CSV import of attribute option to throw exception when the attribute is not known
- Fixed the CSV export of attributes to avoid to export the virtual group 'Other'
- Prevent considering 0 as a null value when importing metric data
- Ensured the attribute option validation when edit an option
- Fixed the product CSV export when a metric attribute is exported without unit
- Fixed the missed 'there are unsaved changes' message when I delete an option
- Ensured the ability to change the user catalog locale from user fixtures
- Fixed mass delete and pagination
- Fixed the CSV import of family when an attribute does not exist
- Fixed the CSV import of option when an attribute does not exist
- Fixed the erroneous message on completeness tab to display "not yet calculated" instead of "locale non associated to this channel"
- Fixed the 'null' displayed after a dynamic option creation
- Fixed the quick Export to be able to export all the products
- Ensured that we're able to configure the email to use in monolog handler
- Fixed the missing translation keys
- Fixed the route exception for less/address in prod.log
- Fixed the categories tree get cut off on a long list on categiry management
- Fixed the deletion of an attribute option
- Remove the deprecated fallback property in locale and in locales.yml file
- Avoid to recalculate the completeness when I add some products to one or more group with the mass-edit wizard
- Fixed the unique attributes validation during product CSV imports
- Fixed the exception on file_get_content if the image doesn't exist anymore
- Ensure the required property for an identifier when importing attributes
- Fixed the error message when the family is not known when importing products
- Removed useless ```app/entities``` directory

## BC breaks
- Add an argument HydratorInterface in ProductDatasource constructor (MongoDBODM support)
- Add an argument $adapterClass (string for FQCN) in ProductFilterExtension and OrmFilterExtension constructors (MongoDBODM support)
- Remove deprecated fallback property of Locale entity
- Add a generateProductCompletenesses method on CompletenessGeneratorInterface, to generate completeness for one product
- Add setCompletenesses and getCompletenesses method on ProductInterface and Product class
- Add methods getProductQueryBuilder, deleteProductIds methods in ProductRepositoryInterface
- Remove methods setLocale/getLocale, setScope/getScope, setConfiguration/getConfiguration from ProductRepositoryInterface
- Remove methods setLocale/getLocale, setScope/getScope from ProductManager
- Move findAllByAttributes and findOneByWithValues from FlexibleEntityRepositoryInterface to ProductRepositoryInterface
- Move setFlexibleQueryBuilder, findAllByAttributes, findOneByWithValues, getFlexibleQueryBuilder, addJoinToValueTables, findAllByAttributesQB from FlexibleEntityRepository to ProductRepository (ORM)
- Move FilterBundle/Filter/ScopeFilter.php, ProductCompletenessFilter.php, ProductGroupsFilter.php, CategoryFilter.php -> FilterBundle/Filter/Product/ScopeFilter.php, CompletenessFilter, GroupsFilter.php, CategoryFilter.php
- Move FilterBundle/Resources/public/js/datafilter/filter/scope-filter.js, category-filter.js -> FilterBundle/Resources/public/js/datafilter/filter/product_scope-filter.js, product_category-filter.js
- Move FilterBundle/Filter/Flexible/FilterUtility.php -> Filter/ProductFilterUtility.php, remove the flexibleEntityName argument of applyFlexibleFilter, rename applyFlexibleFilter to applyFilterByAttribute
- ProductValueNonBlank renamed to ProductValueComplete
- Remove the AclHelper $aclHelper argument from the DataGridBundle/Extension/Pager/Orm/Pager.php constructor
- Moved CustomEntityBundle to its own repository
- Move `FlexibleEntityBundle/Doctrine/*` -> `CatalogBundle/Doctrine/ORM/*`, rename `FlexibleQueryBuilder*` to `ProductQueryBuilder*`, specialize the implementation and pass the CatalogContext as constructor argument
- Changes in the implementation of storing datagrid state - adding 'pim/datagrid/state-listener' to the datagrid configuration is no longer required, instead, the grid should be rendered with dataGrid.renderStatefulGrid()
- Move `FilterBundle/Filter/Flexible/*` -> `FilterBundle/Filter/ProductValue/*`
- Remove unused FilterBundle/Filter/ProductValue/EntityFilter
- Replace FlexibleManager by ProductManager in ContextConfigurator constructor arguments
- Replace tag `pim_flexibleentity.attributetype` by `pim_catalog.attribute_type`
- Replace service `@pim_flexibleentity.validator.attribute_constraint_guesser` by `@pim_catalog.validator.attribute_constraint_guesser`
- Replace the use of FlexibleValueInterface by ProductValueInterface in AttributeTypeInterface and AbstractAttributeType
- Update ProductValueInterface, add getData, setData and getAttribute methods
- Move `DataGridBundle/Extension/Formatter/Property/*` to `DataGridBundle\Extension\Formatter\Property\ProductValue\*`
- Use CatalogContext and not ProductManager as constructor argument in AddParametersToProductGridListener
- Move mass export in specific controller
- Add an affectsCompleteness method to MassEditActionInterface to indicate whether performing the mass action requires recalculating the product completeness
- Remove DeleteMassActionHandler, replaced by ProductDeleteMassActionHandler
- Change product REST API data and url format
- Remove incomplete REST API for getting multiple products
- Remove Router dependency from json ProductNormalizer
- Replace RegistryInterface with ManagerRegistry in controllers - retrieving the ObjectManager from the AbstractController now requires passing the class name (AbstractDoctrineController::getManagerForClass())
- Change Completeness Manager and Repository function names to something more coherent (generateMissingForxxx)
- Move `DataGridBundle/Extension/Sorter\Orm\FlexibleFieldSorter` to `DataGridBundle/Extension/Sorter/Product/ValueSorter`
- Move `DataGridBundle/Extension/Sorter/Orm/FlexibleFieldSorter` to `DataGridBundle/Extension/Sorter/Product/ValueSorter`
- Move `DataGridBundle/Extension/Selector/Orm/*` to `DataGridBundle/Extension/Selector/Orm/Product` and `DataGridBundle/Extension/Selector/Orm/ProductValue`
- ProductRepository does not extend anymore FlexibleEntityRepository, getFlexibleConfig/setFlexibleConfig have been replaced by getConfiguration/setConfiguration
- Change mass action route for products and create own controller for these mass actions
- Add a MassActionHandlerRegistry for mass action handlers services (works with handler alias)
- Rename ProductDeleteMassActionHandler to DeleteMassActionHandler
- Create MassActionHandlerInterface instead of using OroPlatform one
- Change MassActionDispatcher::dispatch parameters
- Replace `@pim_datagrid.datasource.product.result_record.hydrator` by `@pim_datagrid.datasource.result_record.hydrator.product` and same for class parameter
- Move mass action handlers to its own `Handler` directory
- Create PimDatasourceInterface extending OroDatasourceInterface
- Use PimVersioningBundle:Version for all entity audits instead of OroDataAuditBundle:Audit, replace AuditManager with VersionManager, drop AuditBuilder and refactor listeners that create object versions
- Redefine DeleteMassAction, EditMassAction and ExportMassAction
- Remove data_identifier property defined on datagrid.yml for mass actions
- Rename parameter $queryBuilder as $qb in HydratorInterface
- Add findFamilyCommonAttributeIds and findValuesCommonAttributeIds methods to ProductRepository interface
- Remove queryBuilder property from MassEditActionController and remove $request from each action
- Remove queryBuilder from methods initialize and perform in AbstractMassEditAction and children
- Add setProductsToMassEdit and getProductsToMassEdit in AbstractMassEditAction
- Remove EntityManager property from AddToGroups mass edit action and directly inject GroupRepository
- Remove ProductManager property from Classify mass edit action
- Remove method getProductIdsFromQB from EditCommonAttributes mass edit action
- Remove ProductRepository::findFamilyCommonAttributes() and ProductRepository::findValuesCommonAttributeIds() to replace them by ProductRepository::findCommonAttributeIds()
- Disable global search feature
- Remove the 'searchable' property of AbstractAttribute
- Move ProductRepository::getIdentifier() to attribute repository
- Move CatalogBundle\Entity\Repository\ProductRepository to CatalogBundle\Doctrine\ORM
- Move CatalogBundle\Entity\Repository\AssociationRepository to CatalogBundle\Doctrine\ORM
- Move CatalogBundle\Model\ProductRepositoryInterface to CatalogBundle\Repository
- Move CatalogBundle\Model\AssociationRepositoryInterface to CatalogBundle\Repository
- Move CatalogBundle\Model\CompletenessRepositoryInterface to CatalogBundle\Repository
- EditCommonAttributes class needs the ProductBuilder and ProductMassActionManager now
- Move prepareDBALQuery from ProductRepository to QueryBuilderUtility
- Add a ProductCategoryManager and move here the methods getProductsCountInCategory, getProductIdsInCategory from the ProductManager
- Renamed service writer ids `pim_base_connector.writer.orm.*` -> `pim_base_connector.writer.doctrine.*`
- Replace `@security.context` by `@pim_user.context.user` in `ContextConfigurator`
- Delete the attribute virtual group and the `getVirtualGroup` method of the class `Pim\Bundle\CatalogBundle\Model\AbstractAttribute`
- Render the attribute group mandatory for the creation and the edition of an attribute

# 1.0.2
## Bug Fixes
- Removed hardcoded attribute table from ORM/CompletenessGenerator.php
- Fixed of ProductValue's attributes' exclusion on completeness's computation

# 1.0.1
## Bug Fixes
- Removed hardcoded Attribute from ChainedAttributeConstraintGuesser
- Removed hardcoded Attribute from ValidMetricValidator

# 1.0.0 - "Hare We Go" (2014-03-06)

## Features
- Uservoice integration
- Add a last operations widget on the dashboard
- Add new user as fixtures
- Auto-refresh job execution report page
- Add a checkbox to select all visible rows in entity association grids
- Add colors to channels and use them in scopable field labels to make them more compact

## Improvements
- Load choices for grid filters asynchronously
- Allow adding/removing attributes to mass edit attributes view without a page reload
- Propagate -v option to subcommands of install command
- Fix the versions of dependencies in composer.json
- Undisplay unwanted searchable elements in quick search
- Add icons for category and product in search view
- Prevent hydrating all attributes in the available attributes addition form
- Prevent hydrating all families in the product edition form
- Import conversion units for channels
- Product grid loading performance by hydrating as array and introduce selector extension
- Add a screen to select the attribute type before creating an attribute
- Create check-requirements, assets and database/fixtures commands and simplify install one
- Make documentation tested linking it to our behat scenarios

## Bug fixes
- Fixed non-updated values being displayed in the the audit history
- Fixed attribute group form state not being saved
- Do not display Id as an eligible attribute as label
- Fixed select field missing for scopable simple/multi select attributes in the product form
- Restored missing attributes translation
- Fixed the display of scopable metric attributes in the product edit form
- Fixed regression with resolve target entity on Category entity
- Fixed datepicker in date attribute form
- Fixed unwanted fields appearing in attribute creation form if server-side validation errors are present
- Fixed 500 response when submitting import launch form without selecting file to upload
- Fixed $field_catalogLocale linked bugs
- Fixed scopable value order in product form
- Restored unique variant axis constraint when saving product
- Fixed missing breadcrumbs for edit views
- Fixed lost hashnav when creating an attribute group
- Fixed a bug that prevented saving unchecked checkbox value in product edit form
- Fixed recovered attributes on mass edit action
- Fixed a bug with tooltips sometimes not appearing due to a conflict between bootstrap and jquery tooltip plugins

## BC breaks
- Remove the date type property of Attribute and simplify the pim_catalog_date attribute type to support date only (not date/datetime/time)
- Remove unnecessary AttributeManagerInterface and AttributeInterface in favor of AbstractAttribute
- Rename findByWithSortedAttribute to findOneByWithValues, add pre-select attributes and related translations to reduce number of lazy loaded queries when edit a product
- Rename findByWithAttributes to findAllByAttributes
- MeasureBundle has been moved from the BAP to an external repository (akeneo/measure-bundle).
- BatchBundle has been moved from the BAP to an external repository (akeneo/batch-bundle).
- Remove magic setter access to value (ex: $product->setDescription()), as it has multiple conceptual and
technical flaws (attribute codes are data, not a freeze structure, needed to maintain an full attribute cache in product
that made the entity too smart for its own good and created performances problem)
- Remove Product::createValue(). Can be replaced by calling ProductManager::createFlexibleValue() and setting attribute, scope and locale on the created value.
- Product datagrid, hydrate rows as arrays (in place of objects) to reduce the loading time
- Datagrid configuration, remove [flexible_entity] config to avoid to define the used entity twice
- Rename and move src/Pim/Bundle/EnrichBundle/Resources/views/Completeness/_datagridCompleteness.html.twig => DataGridBundle/Resources/views/Property/completeness.html.twig
- Delete classes ConfigureAssociationProductGridListener and AssociationProductColumnsConfigurator, we now use ConfigureFlexibleGridListener to configure product association grid
- Delete the HideColumnsListener, the ColumnConfigurator is now able to add only columns configured by the user
- Rename CompletenessFilter to ProductCompletenessFilter to be consistent, move also the related js file
- Changed signature of ProductRepository::getEligibleProductIds()
- Changed signature of GroupType::__construct()
- Changed signature of AssociationType::__construct()
- Removed AttributeRepository::findallWithGroups()
- Rename grid_extensions.yml, grid_actions.yml, grid_listeners.yml, grid_attribute_types.yml to extensions.yml, actions.yml, event_listeners.yml, attribute_types.yml

# 1.0.0-rc-1 - "Tortoise Beats Hare" (2014-02-06)

## Features
- Completenesses over channels and locales widget
- New command to install the PIM
- Price attributes can be scopable
- Popin to configure product datagrid columns

## Improvements
- Add missing translations
- New grid implementation
- Grids performances
- Quick export of selected products in the grid
- Status column in the product grid
- Thumbnail in product grid for attribute of type image

## Bug fixes
- Bug #658: Export all activated translations even if no value has been set
- Bug PIM-1892: Prevented the form subscriber to remove form fields if not valid
- Downgrade ICU lib to be compatible with RedHat 6 and CentOS 6
- Fix an issue with excessive url length when mass editing many products
- Products grid loaded twice the first time the screen is displayed
- The first tree is not displayed in the mass edit wizard
- When no group type exist, it's not possible to add Variant Group
- Job validation is applied twice (create import/export)
- Validation messages not visible in job creation popin (create import/export)
- Lose hashnav when I create a tree
- Fix completeness calculation on icecat demo dev data
- Application crash on some product validation fail
- In create product popin, no way to search for family (in select 2 field)
- Attribute export in csv shift columns instead of putting blank values
- Error with field_catalogLocale on first load
- Missing translations in page titles
- When adding a new option from product form, the new option is not in the select
- Category edit and page title not updated

## BC breaks
- Change some translation message keys
- Remove GridBundle, add a new DataGridBundle (based on OroPlatform changes)
- Change filters implementations in FilterBundle
- Update all PIM grids to use the new implementation (extensions for filter, sorter, pager, custom datasource, custom cell formatters)
- Rename TranslatableInterface and TranslatableListener by Localizable one in FlexibleEntityBundle
- Rename translatable attribute property by localizable
- FlexibleQueryBuilder has been rewritten to prepare the MongoDB support (add filters and sorters in FlexibleEntityBundle/Doctrine/ORM)
- FlexibleQueryBuilder is injected to ProductRepository
- ProductRepository is injected in ProductManager
- Remove deprecated flexible entity config which is now builded by flexible manager itself (use doctrine meta)
- Move controllers, forms, routing and views from CatalogBundle to EnrichBundle (rename routes, forms, acls, services)
- Introduce a BaseConnectorBundle and move readers, processors, writers, archivers and related configuration from ImportExportBundle
- Introduce a TransformBundle and move cache, converters, encoders, normalizers, transformers and related configuration from ImportExportBundle
- Renaming of services of ImportExport that have been moved (pim_transform_* and pim_base_connector_*)
- Move functionality related to user preferences from LocaleManager and ChannelManager to a dedicated UserContext
- Remove AbstractFlexibleValue::isMatching() method

# 1.0.0-beta-4 - "The Abominable Snow Rabbit" (2014-01-08)

## Features
- Import product associations (CSV)
- New translation mode : Compare and copy values within a product edit form
- Convert metric values into the conversion unit selected for the channel during export
- Allow filtering and sorting by metric values
- Allow to go back to the grid or create another product after saving one
- Add products to many groups through mass edit wizard
- Attribute options fixture
- Product associations fixture
- Fixtures can be in CSV (all fixtures except users and currencies)
- Fixture files can be imported through a command (all fixtures except users and currencies)
- Add quick create popin for jobs
- Add a WYSIWYG editor for TextArea attributes

## Improvements
- Improve the user experience for family management
- Update import / export detail view by adding a summary
- Improve installer to provide different data set (minimal or dev)
- Use a form extension to apply select2 only on specified fields
- Add real time versioning option in product import
- Merge the configuration of import/export job steps in the first tab of the edit view
- Implement save of base unit and data for metric entity
- Metric values are now exported in two distinct columns (value and unit)
- Metric values can now be imported through two distinct columns ([examples](https://github.com/akeneo/pim-community-dev/blob/42371c0d6c70801a4a23a7aa8cf87e18f417c4a8/features/import/import_products.feature#L170-L198))
- Ajaxify the completeness tab of product edit form
- Change the channel switcher and collapse/expand modes on product edit view
- Add a loading mask when loading quick creation form
- Allow to switch configuration between ORM and ODM
- Update OroPlatform from beta-1 to beta-5
- Move Batch Form Types to ImportExport bundle and refactor them to be able to configure any kind of job
- Don't display several UI elements when users don't have the corresponding rights
- Use aliases for subforms, no more manual instanciation to enhance extensibility
- Product prices can now be imported with a single column per currency

## Bug fixes
- Missing pending versionable entities
- Product edit form fails with memory limit for products contained in large groups
- When I delete a filter price or metric and add it again, the filter is not applied
- Translate metric units in select field
- Values of attributes with the type Number are displayed with .0000 on product edit
- Reduce metric field width
- Sort by metric value in product datagrid
- Constraint of unicity for products of a variant group
- When reimporting a product, history for this product shows Create instead of Update
- The completness calculation takes a lot of time after importing in IcecatDemo
- Apply select2 only on needed fields
- Inverse unit and data position for metric form field
- Unwanted popin when try to leave attribute edit view
- Display bug on channel selector with long labels
- Versioning is not called after import
- I can select a root of a tree in the mass-edit wizard
- Products with no completeness do not show in the grid when selecting All products
- Exporting products with an empty file attribute value fails
- The count of Write when I export products is wrong
- Attributes are created even with minimal install
- Error on disallowed decimal on price are not displayed at the right place
- Initial state of completeness filter is wrong
- Search should take account of ACLs
- Oro mapping issue with search item on beta-1
- Locale selector in the product header is sometimes too short
- Allow to remove a translation setting it to empty
- Completeness doesn't take into account currencies of channels

## BC breaks
- Change AbstractAttribute getters that return a boolean value to use the 'is' prefix instead of 'get'. The affected getters are 'getScopable', 'getTranslatable', 'getRequired', 'getUnique'.
- Product, ProductValue, Media and ProductPrice have switched from Pim\Bundle\CatalogBundle\Entity namespace to the Pim\Bundle\CatalogBundle\Model namespace, to pave the way for the MongoDB implementation
- AbstractEntityFlexible getValue method now returns null in place of false when there is now value related to attribute + locale + scope
- Completeness and Product are not linked any more via a Doctrine relationship. We are cutting the links between Product and other entities in order to pave the way to the ability to switch between MongoDB and ORM while using the same API (apart from Product repository).
- Same thing than above for Category
- Relation between Family and Product has been removed from Family side
- Remove PimDataAuditBundle
- Remove PimDemoBundle
- Move product metric in catalog bundle
- Change jobs.yml to batch_jobs.yml and change expected format to add services and parameters
- Rename getStorageManager in flexible manager and change related references
- Rename AttributeTypeManager to AttributeManager and change related references, move createAttribute, createAttributeOption, createAttributeOptionValue from ProductManager to AttributeManager
- Introduce AttributeManagerInterface and remove references to concrete class
- Change attribute type configuration, refactor the attribute type compiler pass and attribute type factory
- Remove getAttributeOptionValueRepository, getFlexibleValueRepository from FlexibleManager
- Attribute fixtures format has changed
- Product associations import/export format has changed.
- Rename Association to AssociationType and all properties/methods linked to this class.
- Rename ProductAssociation to Association
- Rename ProductAttribute to Attribute

# 1.0.0-beta-3 - "Hare Conditioned" (2013-12-04)

## Features
- History of changes for groups and variant groups
- History of changes for import / export profiles
- History of changes for channels
- Allow creating new options for simple select and multiselect attributes directly from the product edit form
- Add a default tree per user
- Introduce command "pim:completeness:calculate" size argument to manage number of completenesses to calculate
- Switching tree to see sub-categories products count and allow filtering on it
- Group types management
- Import/Export product groups (CSV)
- Import/Export associations (CSV)
- Export product associations (CSV)
- Import/Export attributes (CSV)
- Import/Export attribute options (CSV)
- Upload and import an archive (CSV and medias)
- Download an archive containing the exported products along with media
- Add the column "enabled" in the CSV file for products import/export and for versioning

## Improvements
- Export media into separated sub directories
- Separate product groups and variants management
- Display number of created/updated products during import
- Speed up completeness calculation
- Display the "has product" filter by default in the product grid of group edit view
- Display currency label in currencies datagrid
- Disable changing the code of all configuration-related entities
- Merge the directory and filename of export profiles into a single file path property

## Bug fixes
- Mass delete products
- Fix some issues with import ACL translations (issues#484)
- Add a message when trying to delete an attribute used by one or more variant groups instead of throwing an error
- Selection of products in mass edit
- Versioning of installed entities (from demo bundle)
- For csv export of products, only export values related to selected channel and related locales
- Fix locale activation/deactivation based on locales used by channels
- Fix issue with 100 products csv import

## BC breaks
- Command "pim:product:completeness-calculator" has been replaced into "pim:completeness:calculate"
- Refactor in ImportExport bundle for Readers, Writers and Processors

# 1.0.0-beta-2 - "Hold the Lion, Please" (2013-10-29)

## Features
- Manage variant groups
- CRUD actions on groups
- Manage association between groups and products
- CRUD actions on association entities
- Link products with associations
- Import medias from a CSV file containing name of files
- Export medias from a CSV file
- Apply rights on locales for users
- Do mass classification of products
- Define price attribute type with localizable property

## Improvements
- Upgrade to BAP Beta 1
- Homogenize title/label/name entity properties using label
- Mass actions respects ACL
- Improve Import/Export profile view
- Hide access to shortcut to everyone
- Number, date and datetime attributes can be defined as unique values
- Use server timezone instead of UTC timezone for datagrids
- Make upload widget work on FireFox
- Display skipped data errors on job report

## Bug fixes
- Fix sorting channels by categories
- Bug #324 : Translate group label, attribute label and values on locale switching
- Number of products in categories are not updated after deleting products
- Fix dashboard link to create import/export profile
- Fix price format different between import and enrich
- Fix channel datagrid result count
- Fix end date which is updated for all jobs
