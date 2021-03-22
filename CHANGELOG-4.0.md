# 4.0.x

# 4.0.98 (2021-03-16)

# 4.0.97 (2021-03-10)

## Bug fixes

- PIM-9717: Fix 500 error when filtering with invalid identifiers value during an API call

## Technical Improvements

- AOB-1340: Add InMemory implem for GetExistingReferenceDataCodes query 

# 4.0.96 (2021-02-23)

## Bug fixes

- PIM-9696: Associations and categories display according to the ownership rights

# 4.0.95 (2021-02-19)

# 4.0.94 (2021-02-17)

# 4.0.93 (2021-02-12)

# 4.0.92 (2021-02-11)

# 4.0.91 (2021-02-09)

## Bug fixes

- PIM-9665: [Backport] PIM-9533: Update wysiwyg editor's style in order to differentiate new paragraphs from mere line breaks
- PIM-9663: Fix PDF product renderer disregarding permissions on attribute groups (backport of PIM-9649)
- PIM-9669: [Backport] PIM-9610: Force displaying years with 4 digits in dates for every locale

# 4.0.90 (2021-02-02)

# 4.0.89 (2021-01-29)

# 4.0.88 (2021-01-28)

# 4.0.87 (2021-01-26)

## Bug fixes

- PIM-9644: Now using interface in Clean removed attributes command constructor

## Improvements

- PIM-9623: String filter is now able to filter on codes with spaces

# 4.0.86 (2021-01-22)

## Bug fixes

- PIM-9639: Fix sequential edit when selecting All with a filter on parent

# 4.0.85 (2021-01-19)

## Bug fixes

- PIM-9638: Fix security issue in Symfony < 4.4.13 (see https://symfony.com/blog/cve-2020-15094-prevent-rce-when-calling-untrusted-remote-with-cachinghttpclient)
- PIM-9635: Fix case insensitive attribute option code in product validation

# 4.0.84 (2021-01-14)

## Bug fixes

- PIM-9627: Empty Family field when creating a product model with family not present in first page

# 4.0.83 (2021-01-06)

## Bug fixes

- PIM-9611: Improve performance of product indexation by forcing a join due to statistics bias for low cardinality

# 4.0.82 (2021-01-05)

# 4.0.81 (2020-12-23)

## Bug fixes

- PIM-9605: Display the unit label instead of its code in the variant navigation component

# 4.0.80 (2020-12-21)

## Bug fixes

- PIM-9601: Fix compute completeness memory usage

# 4.0.79 (2020-12-18)

## Bug fixes

- PIM-9551: Purge logs when calling the command akeneo:batch:purge-job-execution
- PIM-9602: Fix family dropdown on product model creation modal

# 4.0.78 (2020-12-08)

# 4.0.77 (2020-12-07)

## Bug fixes

- PIM-9513: Fix the use of an unexisting filter on the API so that it does not return an error 500
- PIM-9586: [Backport] PIM-9571 Fix missing items on the invalid data file when importing product models
- PIM-9592: Fix month to seconds conversion

# 4.0.76 (2020-12-02)

# 4.0.75 (2020-11-27)

## Bug fixes

- PIM-9575: Prepend hash to navigate actions URLs
- PIM-9559: Dispatch event when clean removed attributes command is over
- PIM-9573: Create remove_non_existing_product_values job instance at runtime

# 4.0.74 (2020-11-25)

## Bug fixes

- PIM-9568: Fix performance issue when saving a big product group
- RAC-388: Fix fatal error when an attribute is removed then re-created with the same code but another type.

# 4.0.73 (2020-11-23)

## Bug fixes

- PIM-9565: Fix StandardToFlat boolean value converter
- PIM-9550: Add attribute codes as an argument to the command "pim:product:clean-removed-attributes"

# 4.0.72 (2020-11-16)

# 4.0.71 (2020-11-12)

# 4.0.70 (2020-11-09)

## Bug fixes

- PIM-9555: Fix PurgeJobExecutionCommand with 0 day option

# 4.0.69 (2020-11-05)

# 4.0.68 (2020-10-30)

# 4.0.67 (2020-10-28)

## Bug fixes

- PIM-9518: Improve performance of SQL query about fetching images from product model codes
- PIM-9524: improve purge job execution to limit the out of memory errors

# 4.0.66 (2020-10-23)

# 4.0.65 (2020-10-19)

# 4.0.64 (2020-10-09)

# 4.0.63 (2020-10-08)

## Bug fixes

- PIM-9497: Improve performances of SQL query about product model children completeness

# 4.0.62 (2020-10-07)

## Bug fixes

- PIM-9490: [Backport] PIM-9461: Fix display of multiselect fields with a lot of selected options
- PIM-9493: Attributes with no values are well rendered in PDF

## Technical Improvements

- PIM-9472: [Backport] API-1253: Add attr group labels inside attribute end-point + API-1260: Add search filters on attribute groups
- PIM-9470: [Backport] API-1212: Add filters on family search + API-1247: Add filter on category codes + API-1251: Add ability to get categories filtered by parent

# 4.0.61 (2020-10-02)

# 4.0.60 (2020-09-30)

## Technical Improvements

- API-1201: [Backport] Be able to get attributes searching by codes 
- PIM-9469: [Backport] API-1232: Be able to get list of attribute from updated date from the API

# 4.0.59 (2020-09-23)

## Technical Improvements

- PIM-9055: Allow not to drop an existing database during the install process

# 4.0.58 (2020-09-22)

## Bug fixes

- PIM-9455: Make total_fields limit of elasticsearch configurable

# 4.0.57 (2020-09-15)

## Bug fixes

- AOB-1023: Fix duplicated assets menu in PEF
- PIM-9445: Fix boolean attribute is broken on compare/translate when the attribute is localisable or scopable
- PIM-9439: Fix PEF shakes on product with lot of simple/multi select

# 4.0.56 (2020-09-09)

## Bug fixes

- PIM-9430: [Backport PIM-9110] Avoid deadlock error when loading product and product models in parallel with the API

# 4.0.55 (2020-09-03)

# 4.0.54 (2020-08-28)

## Improvement

- PIM-9315: Improve error message when you have no rights on a product

# 4.0.53 (2020-08-27)

## Bug fixes

- PIM-9426: Fix incorrect use of `Akeneo\Tool\Component\Batch\Item\InvalidItemInterface`

# 4.0.52 (2020-08-24)

# 4.0.51 (2020-08-21)

## Bug fixes

- PIM-9416: Add translation keys for mass delete action and corresponding message on the job page

# 4.0.50 (2020-08-20)

## Bug fixes

- PIM-9288: Product completeness was not up to date after deletion of an option for required attribute

# 4.0.49 (2020-08-13)

## Bug fixes

- PIM-9401: Fix Elasticsearch filters with EMPTY operator

# 4.0.48 (2020-08-12)

## Bug fixes

- PIM-9112: Fix empty values cleaner for metric with null values

# 4.0.47 (2020-08-07)

# 4.0.46 (2020-07-31)

## Bug fixes

- PIM-9379: attribute search by label was not working in attribute group add attributes

# 4.0.45 (2020-07-31)

# 4.0.44 (2020-07-28)

# 4.0.43 (2020-07-27)

## Bug fixes

- Fix pdf renderer for image attributes, it was displaying the path instead of the original filename
- PIM-9358: Break down values & properties query with CTE to use less memory
- PIM-9361: Chunk large ES query

# 4.0.42 (2020-07-22)

# 4.0.41 (2020-07-20)

## Bug fixes

- PIM-9359: Fix ES configuration override for dynamic templates

# 4.0.40 (2020-07-13)

## Improvement

-PIM-9330: Sort jobs and connectors by alphabetical order on associated menus on Imports and Exports pages.

## Bug fixes

- PIM-9344: Fix pdf renderer when using pim_catalog_simpleselect

# 4.0.39 (2020-07-08)

## Bug fixes

- PIM-9349: Fix record list alignment when images are loading indefinitely

# 4.0.38 (2020-07-07)

## Bug fixes

- PIM-9343: Fix cannot save product when simple reference entity linked to this product is deleted

# 4.0.37 (2020-07-06)

# 4.0.36 (2020-06-30)

## Bug fixes

- PIM-9328: Add "en_GB" code to display the corresponding locale in the UI.
- PIM-9319: Fix date timezone for a correct display of the dates in the Connection Dashboard

# 4.0.35 (2020-06-22)

## Bug fixes

- Fix fatal error on display product model associations when they have more than 25 products associated

## Enhancements

- PIM-9317: [Backport PIM-9306] Enhance catalog volume monitoring count queries for large datasets

# 4.0.34 (2020-06-17)

## Bug fixes

- PIM-9301: Fix extractUpdatedProductsByConnection query group by issue
- PIM-9294: Fix removal of a validation rule in text attribute edit form
- PIM-9279: Fix missing required attributes display in PEF when an attribute option was deleted
- PIM-9308: Fix infinite scroll in the view selector when some views are filtered

# 4.0.33 (2020-06-11)

## Bug fixes

- PIM-9280: Fix SqlGetConnectorProduct query group by issue with MySQL 8.0.20)

# 4.0.32 (2020-06-08)

## Bug fixes

- PIM-9203: Box shadow appearing on category selector in product grid
- PIM-9250: Display glitch - vertical grey lines appear when opening category tree
- AOB-968: Fix product label rendering on edit form
- CXP-306: Fix the collect of product events
- PIM-9282: Make calling attribute options via API case insensitive
- PIM-9290: Fix product categories loading
- PIM-9291: Fix product updated with a text value made of spaces only

# 4.0.31 (2020-06-01)

## Improvement

-PIM-9106: Improve error message when editing an attribute when it's due to a regular expression

## Bug fixes

- PIM-9277: Fix product computing when moving an attribute in family variant
- PIM-9273: Add a filter to remove the product values of deleted channels and not activated locales

# 4.0.30 (2020-05-28)

## Bug fixes

-PIM-9264: Make "search on..." translatable on Crowdin
-PIM-9269: Add the key "tree.create" and its associated message in the translation file for Crowdin

# 4.0.29 (2020-05-26)

## Bug fixes

- PIM-9260: do not use FPM memory_limit for CommandLauncher

# 4.0.28 (2020-05-20)

# 4.0.27 (2020-05-18)

# 4.0.26 (2020-05-15)

# 4.0.25 (2020-05-14)

## Bug fixes

- PIM-9246: Add a validation on locale codes

# 4.0.24 (2020-05-07)

# 4.0.23 (2020-05-06)

# 4.0.22 (2020-05-05)

## Bug fixes

- PIM-9224: Fix versioning refresh command
- PIM-9227: Fix performance issue on product grid for product model images
- PIM-9181: Backport PIM-9133 to 4.0 (Fix product/product model save when the user has no permission on some attribute groups)

# 4.0.21 (2020-04-29)

## Technical Improvements

- Lock Symfony version on 4.4.7 because of validation issues with 4.4.8

# 4.0.20 (2020-04-27)

# 4.0.19 (2020-04-24)

## Bug fixes

- PIM-9192: Fix error being printed in the response of partial update of products API
- AOB-937: Include additional properties when indexing product models

# 4.0.18 (2020-04-23)

## Bug fixes

- PIM-9190: Mitigates deadlock on product completeness calculation on concurrent API calls
- PIM-9190: Fixes memory leak on product model indexing

# 4.0.17 (2020-04-17)

## Bug fixes

- PIM-9160: Fix the display of the associations list on the product edit form
- PIM-9175: Fix the import of all price values

## Technical Improvements

- PIM-9195: Add extra ImageMagick library to handle SVG files

# 4.0.16 (2020-04-08)

## Bug fixes

- PIM-9164: Improve the display of the validation error message

# 4.0.15 (2020-04-07)

## Technical Improvements

- PIM-9174: PHP_IDE_CONFIG is not dependant from the PIM edition

# 4.0.14 (2020-04-01)

## Technical Improvements

- PIM-9168: Bump symfony/* dependencies to 4.4.7

# 4.0.13 (2020-03-30)

## Bug fixes

- PIM-9164: Fix build property path for localized attributes validation

# 4.0.12 (2020-03-24)

## Bug fixes

- API-1010: Change collect of Audit Data Source events to support Timezone.
- PIM-9134: Remove error notif when a user saves a product and they have no view rights on the attribute used as label

## Technical Improvements

- API-1010: Improve scalability on Audit connection feature.

# 4.0.11 (2020-03-20)

# 4.0.10 (2020-03-18)

# 4.0.9 (2020-03-16)

# 4.0.8 (2020-03-05)

- PIM-9129: Fix missing brightness measure translations

# 4.0.7 (2020-03-04)

## Technical Improvements

- #11583: Remove useless GLOB_BRACE flag from standard Kernel

# 4.0.6 (2020-02-19)

# 4.0.5 (2020-02-14)

# 4.0.4 (2020-02-12)

- PIM-9094: Fix non public controller class for Oro Translation

# 4.0.3 (2020-02-05)

# 4.0.2 (2020-02-04)

## Bug fixes

- PIM-9076: Fix product count on datagrid when more than 10000 products

# 4.0.1 (2020-01-22)

# 4.0.0 (2020-01-15)

## New features

- API-649: Set up Connections
- API-767: Audit Connections

## Bug fixes

- GITHUB-10247: Fix regex to compile the frontend assets. Thanks @liamjtoohey!
- PIM-8894: Change product identifier validation to forbid surrounding spaces

## Technical Improvements
- TIP-1185: Use a single index "product_and_product_model_index" to search on product and product models, instead dedicated product/product model indexes
- TIP-1159: Improve the performance of the calculation of the completeness for the products
- TIP-1225: Improve the performance of the indexation of the products
- TIP-1174: Improve the performance of the indexation of the product models
- TIP-1176: Improve the performance of the computation of product model descendant when updating a product model. The "compute product model descendant" job does not exist anymore. The computation is now done synchronously in the API thanks the improvement done in TIP-1225 and TIP-1174.

## BC breaks

### PHP Server
- Install the GNU Aspell spell-checker package: `aspell`
  Install the dictionaries for Aspell: `aspell-en`, `aspell-es`, `aspell-de`, `aspell-fr`
  Define the binary path for Aspell in the ENV variable: `ASPELL_BINARY_PATH`. (The default path is `aspell`)

### Storage configuration
- Removes the "tmp_storage_dir" parameter. Please use "sys_get_temp_dir()" in your code instead.
- Removes all the directories parameter. Please use the associated Flysystem filesystem in your code instead.

### Elasticsearch

- Remove akeneo_pim_product and akeneo_pim_product_model ES indexes and merge into akeneo_pim_product_and_product_model.

### Doctrine mapping

- The entity `Akeneo\Pim\Enrichment\Component\Product\Model\Completeness` is removed, and is no more an association of the `Product` entity.
  The methods `getCompletenesses` and `setCompletenesses` of a `Product` are removed.
  If you want to access the Completeness of products, please use the dedicated class `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`.

### Codebase

- Change constructor of `Akeneo\UserManagement\Bundle\Controller\Rest\UserController` to add `$securityFacade` as a non-nullable argument.
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`
- Remove service `pim_catalog.manager.completeness`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGenerator`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface`
- Remove service `pim_catalog.completeness.generator`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface` replace by the implementation `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator`
- Remove method `calculate` from `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\PropertiesNormalizer`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelNormalizer`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel\ProductModelPropertiesNormalizer`
- ProductQueryBuilder` should be used to search products and `ProductModelQueryBuilder` should be used to search product models.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\IdFilter` to add `$prefix`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` to remove
    - `string $productClient`
    - `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface` and add
    - `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer` to remove `$productClient` and `$productModelClient`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory` to add:
    - `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface`
    - `Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderWithSearchAggregatorFactory` to add:
    - `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface`
    - `Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber` to remove `$productClient` and `$productModelClient`.
- Rename `Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber` as `Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener`
- Rename `Akeneo\UserManagement\Bundle\EventListener\UserPreferencesSubscriber` as `Akeneo\UserManagement\Bundle\EventListener\UserPreferencesListener`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\LoadEntityWithValuesSubscriber` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\WriteValueCollectionFactory`
- Change constructor of `Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
    - `Doctrine\Common\Persistence\ObjectRepository`
- Change constructor of `Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager`
    - `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
    - `Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface`
    - `Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext`
- Change constructor of `Akeneo\UserManagement\Bundle\EventListener\UserPreferencesSubscriber` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface`
    - `Akeneo\Channel\Component\Repository\ChannelRepositoryInterface`
    - `Akeneo\Channel\Component\Repository\LocaleRepositoryInterface`
    - `Akeneo\UserManagement\Component\Repository\UserRepositoryInterface`
- Change constructor of `Oro\Bundle\DataGridBundle\Datagrid\Manager` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Oro\Bundle\DataGridBundle\Datagrid\Builder`
    - `Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface`
    - `Oro\Bundle\DataGridBundle\Datagrid\Builder\RequestParameters`
- Change constructor of `Oro\Bundle\DataGridBundle\Datagrid\MetadataParser` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add `Symfony\Component\HttpKernel\Fragment\FragmentHandler`
- Change constructor of `Oro\Bundle\PimDataGridBundle\Twig\FilterExtension` to remove `Symfony\Component\DependencyInjection\ContainerInterface` and to add:
    - `Oro\Bundle\DataGridBundle\Datagrid\Manager`
    - `Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator`
    - `Symfony\Component\Translation\TranslatorInterface`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\SequentialEditActionHandler`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\ExportMassActionHandler`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\DeleteProductsMassActionHandler`
- Remove class `Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\DeleteMassActionHandler`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediator`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\WindowMassAction`
- Remove class `Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassAction`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductModelDescendantsSaver`, to remove
    `Doctrine\Common\Persistence\ObjectManager`,
    `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager` and
    `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface` and
    `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface` and
    `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface` and
    `Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface`, and add
    `Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses` and
    `Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver`, to remove `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`
- Delete `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CompletenessNormalizer` (use `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessNormalizer` instead)
- Delete `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\CompletenessCollectionNormalizer` (use `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessCollectionNormalizer` instead)
- Delete `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\CompletenessCollectionNormalizer` (use `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductCompletenessCollectionNormalizer` instead)
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\EntityWithFamilyVariantNormalizer` to add
     `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessWithMissingAttributeCodesCollectionNormalizer`,
     `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator` and remove
     `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface` and `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductNormalizer` to remove
    `Doctrine\Common\Persistence\ObjectManager`, `Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager`, `Akeneo\Channel\Component\Repository\ChannelRepositoryInterface`,
    `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface`, `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface` and `Symfony\Component\Serializer\Normalizer\NormalizerInterface, and to add
    `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessCollectionWithMissingAttributeCodesNormalizer`, `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator` and
    `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductModelNormalizer` to remove
    `Symfony\Component\Serializer\Normalizer\NormalizerInterface` ($incompleteValuesNormalizer), and to add
    `Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculator` and `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface`
- Change constructor of `Oro\Bundle\PimDataGridBundle\Normalizer\ProductAssociationNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Change constructor of `Oro\Bundle\PimDataGridBundle\Normalizer\ProductNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses`
- Removed the following class and the corresponding command:
    - `Akeneo\Pim\Enrichment\Bundle\Command\CreateProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\GetProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\QueryProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\RemoveProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\RemoveWrongBooleanValuesOnVariantProductsBatchCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\RemoveWrongBooleanValuesOnVariantProductsCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\UpdateProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\ValidateObjectsCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\ValidateProductCommand`
    - `Akeneo\Pim\Enrichment\Bundle\Command\AnalyzeProductCsvCommand`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Command\PurgeCompletenessCommand`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Command\PurgeProductsCompletenessCommand`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\CompletenessRemover`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\CalculateCompletenessCommand` to remove
    `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface` and add
    `Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses` and
    `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexerInterface` and
    it does not extend `Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand` anymore
- Update constructor of `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CompleteFilter`, remove `Doctrine\ORM\EntityManagerInterface` and add `Doctrine\DBAL\Connection`
- Remove methods `getCompletenesses` and `setCompletenesses` from `Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface`
- Replace `Akeneo\Pim\Enrichment\Component\Product\Factory\Write` by `Akeneo\Pim\Enrichment\Component\Product\Factory\Read` with method `createByCheckingData`
- Change constructor of `Akeneo\Platform\Bundle\UIBundle\Imagine\FlysystemLoader` to make `Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\MetricNormalizer` to make `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\MetricNormalizer as StandardMetricNormalizer` and `Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\MetricLocalizer` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController` to make `Akeneo\Tool\Bundle\ElasticsearchBundle\Client` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController` to make `Akeneo\Tool\Bundle\ElasticsearchBundle\Client` mandatory
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer` to make `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` mandatory and `string $customFont` becomes the last argument
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueVariantAxisValidator` to make `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetValuesOfSiblings` mandatory and remove `Akeneo\Pim\Enrichment\Component\Product\Repository\EntityWithFamilyVariantRepositoryInterface`
- Replace methods and following interface from `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` by the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` and its new methods:
- Replace interfaces from `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` by the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` and change methods accordingly. Replaced interfaces are:
    - `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`
    - `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface`
    - `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
- Class `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` now implements the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface` instead of
    `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface` and `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
- Class `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer` now implements the single interface `Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface` instead of
    `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`, `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface` and `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer` to remove `$indexType`.
- Commands `Akeneo\Pim\Enrichment\Bundle\Command\IndexProductCommand` and `Akeneo\Pim\Enrichment\Bundle\Command\IndexProductModelCommand` do not extend `Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand` anymore
- Remove class `Akeneo\Pim\Enrichment\Bundle\EventSubscriberAddBooleanValuesToNewProductSubscriber`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\AbstractEntityWithFamilyValuesFiller` to add `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyVariantValuesFiller` to add `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory`
- Update interface `Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface` and class `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct`: `setIdentifier` method now takes a `string` as argument.
- Class `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsIndexer` does not implements these interfaces anymore:
    `Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface` and
    `Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface` and
    `remove` and `removeAll` methods are removed from this class
- Update class `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsIndexer`:
    - Remove implementation of `Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface`
    - Remove implementation of `Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface`
    - Adds parameter `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer` to
    - remove `Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface`
    - remove `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
    - add `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface`, and its implementations `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractCompleteness` and `Akeneo\Pim\Enrichment\Component\Product\Model\Completeness`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\IncompleteValuesNormalizer`
- Remove classes `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValue`, `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollection`, `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollectionFactory`,
    `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollection` and `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\IncompleteValueCollectionFactory`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer` to add `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` (attribute option repository)
- Remove class `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CompleteFilter`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\PRoductModelPropertiesNormalizer`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterData`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterInterface`
- Remove class `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeProductModelDescendantsSubscriber` and replace it by
    - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ComputeProductAndAncestorsSubscriber` and
    - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete\ComputeProductAndAncestorsSubscriber`
- Remove class `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductModelsSubscriber` and replace it by
    - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnSave\ComputeProductAndAncestorsSubscriber` and
    - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\OnDelete\ComputeProductAndAncestorsSubscriber`
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\LocalizableSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption\LocalizableSubscriber` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\CheckChannelsOnDeletionSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete\CheckChannelsOnDeletionSubscriber` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Storage\RemoveCategoryFilterInJobInstanceSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete\RemoveCategoryFilterInJobInstanceSubscriber`
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\UpdateIndexesOnCategoryDeletion` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete\UpdateIndexesOnCategoryDeletion`
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave\ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree`
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\InitCompletenessDbSchemaSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db\InitCompletenessDbSchemaSubscriber`
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeEntityRawValuesSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\ComputeEntityRawValuesSubscriber` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\LoadEntityWithValuesSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\LoadEntityWithValuesSubscriber` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeCompletenessOnFamilyUpdateSubscriber` to `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family\ComputeCompletenessOnFamilyUpdateSubscriber` and mark it as final
- Remove class `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeProductModelDescendantsSubscriber`
- Remove class `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductModelsSubscriber`
- Update class `Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit\ProductAndProductModelWriter` to remove
    - `Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface`,
    - `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`,
    - `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface` and
    - `string $jobName`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductModelDescendantsSaver`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Job\ComputeProductModelsDescendantsTasklet`
- Remove class `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\IndexProductsSubscriber`,
    replaced by `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ComputeProductsAndAncestorsSubscriber`
    and `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete\ComputeProductsAndAncestorsSubscriber`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\AscendantCategories` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\AscendantCategories` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\AttributeIsAFamilyVariantAxis` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute\AttributeIsAFamilyVariantAxis`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountEntityWithFamilyVariant` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family\CountEntityWithFamilyVariant`, mark it as final and change constructor:
    - remove `Doctrine\ORM\EntityManagerInterface`
    - add `Doctrine\DBAL\Connection`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountImpactedProducts` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\CountProductsWithFamily` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family\CountProductsWithFamily`, mark it as final, and change constructor:
    - remove `Doctrine\ORM\EntityManagerInterface`
    - add `Doctrine\DBAL\Connection`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\DescendantProductIdsQuery` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\DescendantProductIdsQuery` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\DescendantProductModelIdsQuery` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\DescendantProductModelIdsQuery` and mark it as final
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\FindAttributesForFamily` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family\FindAttributesForFamily` and change constructor:
    - remove `Doctrine\ORM\EntityManagerInterface`
    - add `Doctrine\DBAL\Connection`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\GetAssociatedProductCodesByProductFromDB` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetAssociatedProductCodesByProductFromDB`, mark it as final, and change constructor:
    - remove `Doctrine\ORM\EntityManagerInterface`
    - add `Doctrine\DBAL\Connection`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\GetAttributeOptionsMaxSortOrder` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute\GetAttributeOptionsMaxSortOrder`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\GetDescendentCategoryCodes` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\GetDescendentCategoryCodes`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\SqlGetValuesOfSiblings` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlGetValuesOfSiblings`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\VariantProductRatio` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\VariantProductRatio`, mark it as final and change constructor:
    - remove `Doctrine\ORM\EntityManagerInterface`
    - add `Doctrine\DBAL\Connection`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\CountVariantProducts` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\CountVariantProducts`
- Move class from `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute\AttributeIsAFamilyVariantAxis` to `Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\AttributeIsAFamilyVariantAxis`
- Move class from `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\CountProductModelsAndChildrenProductModels` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\CountProductModelsAndChildrenProductModels`
- Move class from `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetCategoryCodesByProductModelCodes` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetCategoryCodesByProductModelCodes`
- Move class from `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetGroupAssociationsByProductModelCodes` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetGroupAssociationsByProductModelCodes`
- Move class from `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetProductAssociationsByProductModelCodes` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetProductAssociationsByProductModelCodes`
- Move class from `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetProductModelsAssociationsByProductModelCodes` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetProductModelsAssociationsByProductModelCodes`
- Move class from `Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetValuesAndPropertiesFromProductModelCodes` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetValuesAndPropertiesFromProductModelCodes`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductController` to add `Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\BatchEventSubscriberInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductModelController` to add `Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\BatchEventSubscriberInterface`
- Move all factory classes in `Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value`
- Move class from `Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ValueCollectionFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactory`
- Move class from `Akeneo\Pim\Enrichment\Component\Product\Factory\Read\WriteValueCollectionFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory`
- Move all factory classes in `Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value` to `Akeneo\Pim\Enrichment\Component\Product\Factory\Value`
- Move class from `Akeneo\Pim\Enrichment\Component\Product\Factory\Read\ValueCollectionFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactory`
- Move class from `Akeneo\Pim\Enrichment\Component\Product\Factory\Read\WriteValueCollectionFactory` to `Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ValuesController` to add `Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\EditAttributesProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface` and `Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization\ProductProcessor` to
    - remove `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface`
    - add `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\QuickExport\ProductAndProductModelProcessor` to
    - remove `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface`
    - add `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface` (as `fillMissingProductModelValues`)
    - add `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface` (as `fillMissingProductValues`)
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi` to
    - remove `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface`
    - add `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi` to
    - remove `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface`
    - add `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface` and its concrete implementations:
    `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteChecker`, `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\MediaCompleteChecker`,
    `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\MetricCompleteChecker`, `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\PriceCompleteChecker`
    and `Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\SimpleCompleteChecker`
- Change constructors of `Akeneo\Platform\Bundle\ImportExportBundle\Controller\ExportExecutionController` and `Akeneo\Platform\Bundle\ImportExportBundle\Controller\ExportExecutionController` to remove
  - `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface`,
  - `Symfony\Component\Translation\TranslatorInterface`,
  - `Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler`,
  - `Akeneo\Tool\Bundle\BatchBundle\Manager\JobExecutionManager` and
  - `$jobType`
- Remove class `Akeneo\Platform\Bundle\ImportExportBundle\Controller\ExportProfileController`
- Remove class `Akeneo\Platform\Bundle\ImportExportBundle\Controller\ImportProfileController`
- Remove class `Akeneo\Platform\Bundle\ImportExportBundle\Controller\JobProfileController`
- Remove class `Akeneo\Platform\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType`
- Remove class `Akeneo\Platform\Bundle\ImportExportBundle\Form\Subscriber\JobInstanceSubscriber`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\AbstractEntityWithFamilyValuesFiller`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyVariantValuesFiller`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\ProductValuesFiller`
- Remove method `addAttribute` from `Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface`
- Remove interface `Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\EntityWithFamilyValuesFillerInterface`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\ScalarValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\MetricValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\PriceCollectionValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\OptionValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\OptionsValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\MediaValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\DateValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\ReferenceDataValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\ReferenceDataCollectionValueFactory`
- Remove class `Akeneo\Pim\Enrichment\Component\Product\Factory\PriceFactory`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\BindAssociationTargetsSubscriber`
- Remove class `Akeneo\Pim\Enrichment\Bundle\Form\Type\GroupType`
- Remove class `Akeneo\UserManagement\Bundle\Form\Event\UserFormBuilderEvent`
- Remove class `Oro\Bundle\DataGridBundle\Datasource\Orm\ConstantPagerIterableResult`
- Remove class `Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResult`
- Remove interface `Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface`
- Remove methods `getCurrentMaxLink`, `getLinks`, `haveToPaginate`, `getCursor`, `setCursor`, `getObjectByCursor`, `getCurrent`, `getNext`, `getPrevious`, `getFirstIndex`, `getLastIndex`, `getFirstPage`, `getLastPage`, `getNextPage`, `getPreviousPage`, `getMaxPageLinks`, `setMaxPageLinks`, `isFirstPage`, `isLastPage`, `current`, `key`, `next`, `rewind`, `valid`, `count`, `serialize`, `unserialize` from
  - `Oro\Bundle\DataGridBundle\Extension\Pager\AbstractPager`
  - `Oro\Bundle\DataGridBundle\Extension\Pager\DummyPager`
  - `Oro\Bundle\DataGridBundle\Extension\Pager\Orm\Pager`
  - `Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface`
  - `Oro\Bundle\PimDataGridBundle\Extension\Pager\AbstractPager`
  - `Oro\Bundle\PimDataGridBundle\Extension\Pager\Orm\Pager`
- Remove class `Oro\Bundle\DataGridBundle\ORM\Query\BufferedQueryResultIterator`
- Remove class `Oro\Bundle\PimDataGridBundle\EventSubscriber\DefaultViewSubscriber`
- Move class from `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FamilyVariantsByAttributeAxes` to `Akeneo\Pim\Structure\Bundle\Storage\Sql\FamilyVariantsByAttributeAxes`
- Move class from `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FindAttributeGroupOrdersEqualOrSuperiorTo` to `Akeneo\Pim\Structure\Bundle\Storage\Sql\SqlFindAttributeGroupOrdersEqualOrSuperiorTo` and change constructor to
    - remove `Doctrine\ORM\EntityManagerInterface`
    - add `Doctrine\DBAL\Connection`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Job\EnsureConsistentAttributeGroupOrderTasklet` to
    - remove `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query\FindAttributeGroupOrdersEqualOrSuperiorTo` (implementation class)
    - add `Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo` (interface)
- Change constructor of `Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui\JobTrackerController` to remove
    - `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface`
    - `Symfony\Component\Translation\TranslatorInterface`
    - `Symfony\Component\Serializer\SerializerInterface`
    - `Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager`
- Update interface `src/Akeneo/UserManagement/Component/Model/UserInterface` and class `src/Akeneo/UserManagement/Component/Model/User`: add `defineAsApiUser` and `isApiUser` methods.

### CLI Commands

The following CLI commands were deleted:
- `pim:product:create`
- `pim:product:get`
- `pim:product:query`
- `pim:product:remove`
- `pim:product:validate`
- `pim:product:update`
- `pim:catalog:remove-wrong-boolean-values-on-variant-products-batch`
- `pim:catalog:remove-wrong-boolean-values-on-variant-products`
- `pim:objects:validate`
- `pim:connector:analyzer:csv-products`
- `pim:completeness:purge`
- `pim:completeness:purge-products`

If you want to purge the completeness in order to recalculate it, please use the dedicated command `pim:completeness:calculate`

### Services

- Update `pim_catalog.factory.product_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_identifier_cursor_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_identifier_cursor_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_model_identifier_cursor_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Remove `akeneo_elasticsearch.client.product` from `pim_catalog.elasticsearch.indexer.product`
- Remove `akeneo_elasticsearch.client.product` and `akeneo_elasticsearch.client.product_model` from `pim_catalog.elasticsearch.indexer.product_model`
- Update `akeneo.pim.enrichment.follow_up.completeness_widget_query` to use `akeneo_elasticsearch.client.product_and_product_model`
- Remove `akeneo_elasticsearch.client.product` and `akeneo_elasticsearch.client.product_model` from `pim_catalog.event_subscriber.category.update_indexes_on_category_deletion`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.query.product_model_query_builder_search_after_size_factory_external_api` to use `Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder`
- Update `pim_catalog.query.product_model_query_builder_from_size_factory_external_api` to use `Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder`
- Update `pim_catalog.query.product_model_query_builder_factory` to use `pim_catalog.query.product_model_query_builder`
- Update `pim_catalog.query.product_model_query_builder_from_size_factory` to use `pim_catalog.query.product_model_query_builder`
- Update `pim_catalog.query.product_model_query_builder_search_after_size_factory` to use `pim_catalog.query.product_model_query_builder`
- Remove `pim_catalog.query.product_and_product_model_query_builder_factory.with_product_and_product_model_cursor`
- Update `akeneo.pim.enrichment.query.product_query_builder_from_size_factory.with_product_identifier_cursor` to use `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory`
- Update `pim_enrich.query.product_query_builder_from_size_factory.with_product_and_product_model_from_size_cursor` to use `pim_catalog.query.elasticsearch.product_and_model_query_builder_factory`
- Update `pim_enrich.query.product_query_sequential_edit_builder_factory` to use `pim_catalog.query.product_and_product_model_query_builder.class`
- Rename `pim_versioning.event_subscriber.addversion` to `pim_versioning.event_listener.addversion`
- Rename `pim_user.event_listener.user_preferences` to `pim_user.event_subscriber.user_preferences`
- Remove `pim_enrich.normalizer.completeness` (use `pim_enrich.normalizer.product_completeness` instead)
- Remove `pim_enrich.normalizer.completeness_collection` (use `pim_enrich.normalizer.product_completeness_collection` instead)
- Remove `pim_catalog.normalizer.indexing_product.product.completeness_collection` (use `pim_catalog.normalizer.indexing_product.product.product_completeness_collection` instead)
- Update service `pim_pdf_generator.renderer.product_pdf` to use `pim_catalog.repository.cached_attribute_option` as the 9th argument and `pim_pdf_generator_font` as the 10th
- Update service `pim_catalog.validator.constraint.unique_variant_axes` to remove `pim_catalog.repository.entity_with_family_variant`
- Remove duplicated service definitions for `pim_catalog.validator.constraint.family_variant_axes` and `pim_catalog.validator.constraint.immutable_family_variant_axes` in `src/Akeneo/Pim/Enrichment/Bundle/Resources/cofig/validators.yml`
- Remove `pim_catalog.event_subscriber.add_boolean_values_to_new_product`
- Remove `pim_enrich.normalizer.incomplete_values`
- Remove `pim_catalog.entity_with_family.required_value_collection_factory` and `pim_catalog.entity_with_family.incomplete_value_collection_factory`
- Remove `pim_catalog.event_subscriber.compute_product_model_descendants`
- Remove `pim_catalog.event_subscriber.index_product_models`
- Remove `pim_catalog.job.job_parameters.default_values_provider.compute_product_models_descendants`
- Remove `pim_catalog.tasklet.compute_product_models_descendants`
- Remove `pim_catalog.step.compute_product_models_descendants`
- Remove `pim_catalog.job.compute_product_models_descendants`
- Remove `pim_connector.step.csv_compute_product_models_descendants.import` from `pim_connector.job.csv_product_model_import`
- Remove `pim_connector.step.xlsx_compute_product_models_descendants.import` from `pim_connector.job.xlsx_product_model_import`
- Remove `pim_connector.processor.denormalization.product_model_loader`
- Remove `pim_catalog.saver.product_model_descendants`
- Remove `pim_connector.step.csv_compute_product_models_descendants.import`
- Remove `pim_connector.step.csv_compute_product_models_descendants.import` from `pim_installer.job.fixtures_product_model_csv`
- Remove `pim_connector.step.xlsx_compute_product_models_descendants.import`
- Remove `pim_connector.writer.database.product_model_descendants`
- Remove from `pim_enrich.writer.database.product_and_product_model_writer`:
    - `security.token_storage`
    - `akeneo_batch_queue.launcher.queue_job_launcher`
    - `akeneo_batch.job.job_instance_repository`
    - `string $jobName`
- Remove `pim_catalog.event_subscriber.index_products`
- Rename service `pim_catalog.doctrine.query.attribute_is_an_family_variant_axis` in `akeneo.pim.structure.query.attribute_is_an_family_variant_axis`
- Remove `pim_catalog.factory.value`, please use `akeneo.pim.enrichment.factory.value` instead
- Remove `pim_catalog.factory.value.text`
- Remove `pim_catalog.factory.value.textarea`
- Remove `pim_catalog.factory.value.number`
- Remove `pim_catalog.factory.value.boolean`
- Remove `pim_catalog.factory.value.identifier`
- Remove `pim_catalog.factory.value.metric`
- Remove `pim_catalog.factory.value.price_collection`
- Remove `pim_catalog.factory.value.option`
- Remove `pim_catalog.factory.value.options`
- Remove `pim_catalog.factory.value.file`
- Remove `pim_catalog.factory.value.image`
- Remove `pim_catalog.factory.value.date`
- Remove `pim_reference_data.factory.product_value.reference_data`
- Remove `pim_reference_data.factory.product_value.reference_data_collection`
- Remove `pim_catalog.factory.price`
- Rename service `pim_catalog.doctrine.query.find_attribute_group_orders_equal_or_superior_to` in `akeneo.pim.structure.query.find_attribute_group_orders_equal_or_superior_to`
- Rename service `pim_catalog.doctrine.query.find_family_variants_identifiers_by_attribute_axes` in `akeneo.pim.structure.query.find_family_variants_identifiers_by_attribute_axes`
