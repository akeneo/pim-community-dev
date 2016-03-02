# 1.2.37 (2015-08-18)

## Bug fixes
- PIM-4748: remove left joins to fix perf issue with many locales
- PIM-4780: performance issue due to numerous label

# 1.2.36 (2015-07-06)

## Bug fixes
- PIM-4494: Fix js memory leak on a product edit form with scopable attributes

# 1.2.35 (2015-05-29)

## Bug fixes
- PIM-4227: Disable product versionning on category update (never used and very slow)

# 1.2.34 (2015-05-27)

## Bug fixes
- PIM-4223: Fix grid sorting order initialization (changed to be consistent with Platform behavior)

# 1.2.33 (2015-03-16)

# 1.2.32 (2015-03-11)

## Bug fixes
- PIM-3786: Attribute type should not be blank for import
- PIM-3437: Fix applying datagrid views and columns when not using hash navigation
- PIM-3844: Create popin keeps state in memory

# 1.2.31 (2015-03-06)

# 1.2.30 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.2.29 (2015-02-24)

# 1.2.28 (2015-02-20)

## Bug fixes
- PIM-3790: Fix WYSIWYG on folded scopable elements
- PIM-3785: Can not export Products/Published due to null medias

# 1.2.27 (2015-02-13)

## Bug fixes
- PIM-3779: Fix multiple WYSIWYG on same textarea element

# 1.2.26 (2015-02-12)

## Bug fixes
- PIM-3761: Fix WYSIWYG onClick behaviour, event correctly bind
- PIM-3632 : Correctly show scopable attribute icons on scope change

# 1.2.25 (2015-02-04)

## Bug fixes
- PIM-3718: load tinymce only on textarea click

# 1.2.24 (2015-01-28)

## Bug fixes
- PIM-3712: Fix installation issue related to the tag of gedmo/doctrine-extensions v2.3.11, we freeze to v2.3.10

# 1.2.23 (2015-01-23)

## Bug fixes
- PIM-3664: Fix product media stacktrace regression on missing media on filesystem during an export
- PIM-3677: Fix `Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection` saving problem

# 1.2.22 (2015-01-21)
- Crowdin Updated translations

# 1.2.21 (2015-01-16)

## Bug fixes
- PIM-3615: Context of the grid not applied in product form for an attribute type Date
- PIM-3638: Fix doctrine/cache 1.3.1 to fix Oro FilesystemCache issue

# 1.2.20 (2015-01-14)

## Bug fixes
- PIM-3603 Trigger saving wysiwyg editor contents when submitting product form manually

# 1.2.19 (2015-01-09)

## Bug fixes
- PIM-3556: Fix memory leak on versionning
- PIM-3548: Do not rely on the absolute file path of a media

# 1.2.18 (2014-12-23)

## Bug fixes
- PIM-3533: Fix wrong keys being generated for empty price attributes in normalized product snapshots
- PIM-3558: Fix order of options for multiselect attribute in product versionning and csv product export

## BC breaks
- PIM-3558: in the exported product csv file, we apply the sort order defined by the user to sort the options of a multiselect

# 1.2.17 (2014-12-19)
- PIM-3550: force the version of "doctrine/annotations" to "v1.2.1" to avoid the BC Break introduced with v1.2.2

# 1.2.16 (2014-12-17)

## Bug fixes
- PIM-3447: Enforce max database length limit on identifier, text and textarea attribute values
- PIM-3471: Add an error log when the max number of indexes is reached for the mongo product collection (MongoResultException is raised since Mongo 2.6.*)
- PIM-3369: Check on import if the couple channel/local exist
- PIM-3368: Add association type check on association import
- PIM-3377: Add a check if the specific locale exists on imports, and skip unused attribute column for locale specific on exports
- PIM-3458: When creating an attribute group, automatically set the sort order to the last one
- PIM-3420: Remove update guessers on attributes and attributes option to fix the versionning memory leak

## BC breaks
- PIM-3368: Add AssociationType class argument to the `Pim\Bundle\TransformBundle\Transformer\AssociationTransformer` constructor

## Improvements
- PIM-3448: Add the method `getAttributeGroupsFromAttributeCodes` in the `Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository`

# 1.2.15 (2014-12-10)

## Bug fixes
- PIM-3473: Fix date picker year range selection over next year
- PIM-3475: Fix attribute options sort order in import/export

## BC breaks
- Export of attribute options in CSV now include a sort_order column

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
