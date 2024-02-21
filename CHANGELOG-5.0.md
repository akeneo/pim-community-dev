# 5.0.x

# 5.0.118 (2022-11-18)

# 5.0.117 (2022-11-10)

## Bug fixes

- PIM-10711: Fix AbstractInvalidItemWriter does not pad empty values for trailing empty column

# 5.0.116 (2022-11-07)

## Bug fixes

- PIM-10671: Fix slowness on product association page with a lot of categories

# 5.0.115 (2022-10-18)

## Bug fixes

- PIM-10651: replace a technical exception by a new business exception

# 5.0.114 (2022-09-20)

# 5.0.113 (2022-09-02)

# 5.0.112 (2022-09-01)

## Bug fixes

- PIM-10591: Fix download log should not be possible when job is running
- PIM-10610 [Backport PIM-10504]: Fix Job execution stuck in status in progress or stopping
- PIM-10608 [Backport PIM-10584]: Fix measurement values for volume flow conversion 

# 5.0.111 (2022-08-25)

## Bug fixes

- PIM-10601 [Backport PIM-9718]: Fix format of integer values for number attributes with decimals allowed
- PIM-10577: Fix copy/paste on wysiwyg textarea links
- PIM-10580 [Backport PIM-10499]: Fix MySQL's out of sort memory errors on variant product and product model edit form

# 5.0.110 (2022-08-23)

# 5.0.109 (2022-08-23)

# 5.0.108 (2022-08-22)

## Bug fixes

- PIM-10587: Fix warnings count in process tracker

# 5.0.107 (2022-08-17)

## Bug fixes

- PIM-10512: Fix empty group labels format for attribute endpoints
- PIM-10555 [Backport PIM-10462]: Fix ComputeFamilyVariantStructureChanges job not launched after import
- PIM-10559 [Backport PIM-10478]: Disable compute_family_variant_structure_changes on familySave

# 5.0.106 (2022-08-08)

## Bug fixes

- PIM-10553: Fix initialization of the associations grid in product edit form
- PIM-10556: [Backport PIM-10214] Fix can't create a measurement attribute if measurement family or unit code is too long

# 5.0.105 (2022-08-03)

## Bug fixes

- PIM-10551: Add filtering on locale specific attributes when fetching values

# 5.0.104 (2022-07-11)

# 5.0.103 (2022-07-11)

## Bug fixes

- PIM-10523: fix CSV import profiles have the `download read file` option in Process Tracker but not XLSX import profiles
- PIM-10506: Fix performance issues on process tracker

# 5.0.102 (2022-07-07)

## Bug fixes

- PIM-10502: Fix API error when trying to post/patch a product with non-existing attribute code that is also a number

# 5.0.101 (2022-07-01)

# 5.0.100 (2022-06-29)

# 5.0.99 (2022-06-21)

# 5.0.98 (2022-06-08)

## Bug fixes

- PIM-10477: [Backport PIM-10220] Fixed issues where association has NaN error
- PIM-10472: Fix attributes list limit in the family mass edit

# 5.0.97 (2022-05-30)

# 5.0.96 (2022-05-24)

# 5.0.95 (2022-05-13)

## Bug fixes

- PIM-10428: [Backport PIM-10049] Add custom strip filter to avoid segmentation fault
- PIM-10439: Add CLI command in order to fix broken order on categories

# 5.0.94 (2022-05-11)

## Bug fixes

- PIM-10419: [Backport] PIM-10350: Fix simple and multi select filtering and comparison

# 5.0.93 (2022-05-03)

## Bug fixes

- PIM-10424: [Backport] Increase product grid filters limit display in user settings

# 5.0.92 (2022-04-28)

# 5.0.91 (2022-04-13)

# 5.0.90 (2022-04-06)

# 5.0.89 (2022-04-01)

## Bug fixes

- PIM-10374: Revert PIM-10333 + Fix category translations are not displayed in the category tree when locale is not xx_XX

# 5.0.88 (2022-03-23)

# 5.0.87 (2022-03-23)

## Bug fixes

- CPM-562 Fix product grid loading when attribute as image has a numeric code
- PIM-10364: Fix broken permissions on Associations with Quantity

# 5.0.86 (2022-03-17)

## Bug fixes

- PIM-10335: Fix locale not saved for localizable attribute in product exports

# 5.0.85 (2022-03-17)

## Bug fixes

- PIM-10430: add missing translation in attribute group import job page
- PIM-10333: Import category without correct locale should be impossible

# 5.0.84 (2022-03-15)

# 5.0.83 (2022-02-28)

## Bug fixes

- PIM-10296: Fix measurement attributes with value zero not displayed correctly in product grid

# 5.0.82 (2022-02-25)

## Bug fixes

- PIM-10294: Display blocked message when user is actually blocked at 5th attempt and not at 6th

# 5.0.81 (2022-02-25)

## Bug fixes

- PIM-10275: Missing translation key when error occured during association deletion

# 5.0.80 (2022-02-23)

## Bug fixes

- PIM-10288: Fix product associations came duplicated in API

# 5.0.79 (2022-02-21)

# 5.0.78 (2022-02-18)

## Bug fixes

- PIM-10270: Fix insufficient limit when fetching a lot of attribute groups in families settings

# 5.0.77 (2022-02-10)

## Bug fixes

- PIM-10258: Increase buffer size to avoid memory size issue for product associations query

# 5.0.76 (2022-02-07)

## Bug fixes

- PIM-10257: Fix content Security Policy error log

# 5.0.75 (2022-02-04)

# 5.0.74 (2022-02-04)

# 5.0.73 (2022-02-04)

## Bug fixes

- PIM-10250: [Backport] PIM-9806: Enable authentication temporary lock to protect against brute force attack

# 5.0.72 (2022-02-04)

# 5.0.71 (2022-02-01)

# 5.0.70 (2022-02-01)

## Bug fixes

- PIM-10248: Fix NOT BETWEEN filter does not work on products and product models (created and updated property)
- PIM-10223: Add missing "s" on "remove-orphans" option in Makefile

# 5.0.69 (2022-01-21)

## Bug fixes

- PIM-10233: [Backport] Refresh ES index after creating a product from the UI in order to well send product created event to event subscriptions

# 5.0.68 (2022-01-17)

# 5.0.67 (2022-01-03)

## Bug fixes

- PIM-10222: Fixed selected category glitch on product grid category filter

# 5.0.66 (2021-12-22)

# 5.0.65 (2021-12-22)

# 5.0.64 (2021-12-17)

# 5.0.63 (2021-12-14)

## Bug fixes

- PIM-10204: Use catalog locale for option labels in simple/multi select attributes

# 5.0.62 (2021-12-10)

# 5.0.61 (2021-12-02)

## Bug fixes

- PIM-10147: Make timezones offset dynamic with summer/winter time change in user interface settings

# 5.0.60 (2021-11-30)

# 5.0.59 (2021-11-26)

## Bug fixes

- PIM-10179: Fix migrations on tables job_execution_queue and pim_datagrid_view

# 5.0.58 (2021-11-23)

## Bug fixes

- PIM-10162: Not all locales are displayed when using compare/translate feature on product

# 5.0.57 (2021-11-08)

## Bug fixes

- PIM-10075: Impossible to classify products in a new tab/window from a right click on the product grid

# 5.0.56 (2021-11-05)

## Bug fixes

- PIM-10141: [Backport] PIM-9711: Check that a category root isn't linked to a user or a channel before moving it to a sub-category
- PIM-10128: Fixed disabled user activation after password reset

# 5.0.55 (2021-11-03)

## Bug fixes

- PIM-10136: [Backport] PIM-9763: Make sure that 2 users can each create a private view with the same name
- PIM-10131: [Backport] PIM-9740: Prevent to delete a channel used in a product export job
- PIM-10133: [Backport EXB-1046]: Prevent to delete a channel used in shared catalog export job
- PIM-10134: Prevent to delete a channel used in a published product export job
- PIM-10132: Update shared catalog export profile when the channel's category is changed

# 5.0.54 (2021-10-29)

# 5.0.53 (2021-10-25)

# 5.0.52 (2021-10-22)

## Bug fixes

- PIM-10040: Fix longtext types instead of json type in old catalogs
- PIM-10053: Changed category tree to open node on label click on Product Export
- PIM-10121: Fix metric to string converter to remove trailing 0

# 5.0.51 (2021-10-18)

## Bug fixes

- PIM-10118: Fix attribute option with numeric code not being translated when exported

# 5.0.50 (2021-10-11)

## Bug fixes

- PIM-10105: Fix PurgeableVersionList no longer keeps every version if it is asked to keep none

# 5.0.49 (2021-10-11)

# 5.0.48 (2021-09-21)

# 5.0.47 (2021-09-20)

## Bug fixes

- PIM-10060: Impossible to edit products in a new tab/window from a right click on the product grid
- PIM-10073: [Backport PIM-9671] DQI de-activation on attribute group is not fully taken into account

# 5.0.46 (2021-09-03)

# 5.0.45 (2021-08-26)

## Bug fixes

- PIM-10011: Fix the categoryId update when changin view

# 5.0.44 (2021-08-16)

# 5.0.43 (2021-07-26)

# 5.0.42 (2021-07-20)

# 5.0.41 (2021-07-19)

# 5.0.40 (2021-07-09)

## Bug fixes

- PIM-10025: [Backport] PIM-9987: Fix product grid count not accurate after specific SKU selection
- PIM-9956: [Backport] PIM-9852: Fix exception during PRE_REMOVE on removeAll cause ES desynchronisation
- PIM-9943: Fix product-grid quality score filter all

# 5.0.39 (2021-07-06)

## Bug fixes

- PIM-9951: Fix wrong locale used by spellcheck when comparing product attributes

# 5.0.38 (2021-07-02)

## Bug fixes

- PIM-9944: Fix attribute group grid search
- PIM-9945: Fix displayed number of elements in attribute group, locale and measurement family grids

# 5.0.37 (2021-07-01)

# 5.0.36 (2021-06-25)

## Bug fixes

- PIM-9931: [Backport] PIM-9678: The time counter is still running despite the job failed
- PIM-9935: [Backport] PIM-9890: Creating Channels with numeric code breaks the PIM

# 5.0.35 (2021-06-22)

## Bug fixes

- PIM-9921: Add a translation for the title of category creation form

# 5.0.34 (2021-06-22)

- PIM-9916: Fix value updating for text, simple select and date attribute used as product export filter not saved

# 5.0.33 (2021-06-18)

# 5.0.32 (2021-06-16)

# 5.0.31 (2021-06-10)

## Bug fixes

- PIM-9876: Fix purge of products old scores in Data Quality Insights
- PIM-9896: Patched symfony/security-core vulnerability

# 5.0.30 (2021-06-04)

## Bug fixes:

- PIM-9895: [Backport] PIM-9707: ES Max query size and add test for the ElasticSearch client chunked bulk index
- PIM-9894: [Backport] PIM-9700: Add batch-size option in index products command and index product-models command

# 5.0.29 (2021-05-31)

## Bug fixes:

- PIM-9882: Fix the display of the grid selector secondary action dropdown

# 5.0.28 (2021-05-28)

## Bug fixes

- PIM-9878: Fix breadcrumb link in Settings > Attribute Groups

# 5.0.27 (2021-05-26)

# 5.0.26 (2021-05-21)

## Bug fixes

- PIM-9839: Fix indexation issue on the 2-way associations

# 5.0.25 (2021-05-19)

- OB-806: Add missing migration on `pim_catalog_completeness` table
- PIM-9865: [Backport] PIM-9771: Export to PDF doesn't export Image

# 5.0.24 (2021-05-07)

# 5.0.23 (2021-05-05)

## BC breaks

- API-1557:
  - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductCreatedAndUpdatedEventSubscriber` implements `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface`.
  - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductModelCreatedAndUpdatedEventSubscriber` implements `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface`.
  - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductModelRemovedEventSubscriber` implements `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface`.
  - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchProductRemovedEventSubscriber` implements `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent\DispatchBufferedPimEventSubscriberInterface`.
  - Public methods have been changed according to the new interface.

# 5.0.22 (2021-04-27)

# 5.0.21 (2021-04-23)

# 5.0.20 (2021-04-22)

# 5.0.19 (2021-04-20)

## Bug fixes

- OB-752: Fix 5.0 memcached package issue

# 5.0.18 (2021-04-15)

# 5.0.17 (2021-04-15)

# 5.0.16 (2021-04-08)

## Bug fixes

- PIM-9799: Improve PEF performance by avoid a useless render

# 5.0.15 (2021-04-06)

# 5.0.14 (2021-04-01)

# 5.0.13 (2021-03-29)

- PLG-137 - Fix filter "Image quality" label to avoid confusion
- PLG-138 - Fix bad "activated" english naming in locales screen
- PLG-139 - Fix bad "activated" english naming in attribute group screen

# 5.0.12 (2021-03-26)

# 5.0.11 (2021-03-24)

# 5.0.10 (2021-03-23)

# 5.0.9 (2021-03-19)

# 5.0.8 (2021-03-17)

# 5.0.7 (2021-03-09)

# 5.0.6 (2021-03-09)

# 5.0.5 (2021-02-19)

## Bug fixes

- AOB-1317: Fix deprecated nested ternary expression
- DAPI-1490: Fix redirect to product grid from DQI dashboard when a default view is activated on the user profile
- PIM-9686: Fix memory leak during "set_attribute_requirements" job
- PIM-9673: Make sure that the job that converts product models into simple products does not fail
- PIM-9727: Add missing query params to hatoas links

# 5.0.4 (2021-02-02)

# 5.0.3 (2021-01-29)

# 5.0.2 (2021-01-29)

## Bug fixes

- DAPI-1477: Fix unstable DQI integration tests

# 5.0.1 (2021-01-08)

## Bug fixes

- DAPI-1470: Fix DateTime bad usage
- DAPI-1469: Fix the size issue with the logo on login page
- PIM-9622: Fix query that can generate a MySQL memory allocation error
- PIM-9620: Fix performance issue on API attributes partial update list

# 5.0.0 (2020-12-31)

## Bug fixes

- PIM-9554: Discrepancy on the user dashboard due to difference between UI locale and catalog locale
- PIM-9486: System Information sections Registered bundles and PHP extensions repeat a high number of times
- PIM-9514: Fix check on API completness for product model
- PIM-9408: Fix attribute group's updated_at field udpate
- TIP-1513: Environment variables declared in the env were not loaded when using a compiled .env file
- PIM-9274: Fix Yaml reader to display the number of lines read for incorrectly formatted files
- TIP-1406: Add a tag to configure a DIC service based on a feature flag
- PIM-9133: Fix product save when the user has no permission on some attribute groups
- Fixes memory leak when indexing product models with a lot of product models in the same family
- PIM-9119: Fix missing warning when using mass edit with parent filter set to empty
- PIM-9114: fix errors on mass action when the parent filter is set to empty
- PIM-9110: avoid deadlock error when loading product and product models in parallel with the API
- PIM-9113: Locale Specific attribute breaks product grid
- PIM-9157: Fix performance issue when loading the data of a product group
- PIM-9163: total_fields limit of elasticsearch should be configurable
- PIM-9197: Make queries in InMemoryGetAttributes case insensitive
- PIM-9213: Fix tooltip hover on Ellipsis for Family Name on creating product
- PIM-9184: API - Fix dbal query group by part for saas instance
- PIM-9289: Display a correct error message when deleting a group or an association
- PIM-9327: PDF generation header miss the product name when the attribute used as label is localizable
- PIM-9324: Fix product grid not loading when asset used as main picture is deleted
- PIM-9356: Fix external api endpoint for products with invalid quantified associations
- PIM-9357: Make rules case-insensitive so it complies with family and attribute codes
- PIM-9362: Adapt System Information twig file for a clear and a correct display of the number of API connections
- PIM-9360: Fix PHP Warning raised in PriceComparator
- PIM-9370: Fixes page freezing with a big number of attribute options
- PIM-9391: Filter empty prices and measurement values
- PIM-9407: Fix glitch in family variant selector if the family variant has no label
- PIM-9425: Fix inaccurate attribute max characters
- PIM-9443: Do not cache extensions.json
- PIM-9454: Fix scalar value type check in PQB filters
- PIM-9460: Fix performance issue on export
- PIM-9461: Fix display of multiselect fields with a lot of selected options
- PIM-9466: Fix selection counter in datagrid
- GITHUB-12578: Fix trailing zeros when formatting numbers
- PIM-9440: Fix locked MySQL tables during removing DQI evaluations without product
- PIM-9476: Fix locale selector behavior on the product edit form when the user doesn't have permissions to edit attributes
- PIM-9478: Allow the modification of the identifier on a variant product
- PIM-9481: Fix the list of product models when trying to get them by family variant
- GITHUB-12899: Fix error shown when importing product models with the same code
- PIM-9491: Translate product grid filters in user additional settings
- PIM-9494: Fix the performances of attribute-select-filter on long lists of AttributeOptions
- PIM-9496: Change date format in the locale it_IT from dd/MM/yy to dd/MM/yyyy
- PIM-9519: Fix translation key for datagrid search field
- PIM-9517: Fix locale selector default value on localizable attributes in product exports
- PIM-9516: Recalculate completeness after a bulk set attribute requirements on families
- PIM-9532: Fix the family selection in mass action when a filter on label is set
- PIM-9535: Fix export with condition on localisable attribute does not work if selected locale is not changed
- PIM-9542: Fix product creation if the family has a numeric code
- PIM-9498: Add translation for 'Mass delete products' job
- PIM-9538: Fix sorting on rule engine list page
- PIM-9499: Fix warning display when a job is running with warnings
- PIM-9545: Fix possible memory leak in large import jobs
- PIM-9533: Update wysiwyg editor's style in order to differentiate new paragraphs from mere line breaks
- PIM-9548: Mitigate deadlock issues on category API
- PIM-9540: Do not strip HTML tags on textarea content before indexing them in ES and fix newline_pattern char filter
- PIM-9539: Fix the display of long attribute labels or codes on variant attributes page
- PIM-9580: Fix conversion operation for ATM, PSI, TORR & MMHG
- PIM-9569: Fix memory usage issue when adding a group to a product
- PIM-9571: Fix missing items on the invalid data file when importing product models
- PIM-9543: Print PDF content with Asian characters
- PIM-9577: Remove empty 'Global settings' tab on following XLSX import: attribute, family, family variant, association type, attribute option, attribute group, group type
- PIM-9590: Fix "Default product grid view" multiple times on user settings page
- CPM-86: Fix undefined tab on job profile edit
- PIM-9596: Fix attribute options manual sorting
- PIM-9598: Fix quick export when the bs_Cyrl_BA locale is used.
- PIM-9612: Fix no image preview for Association with quantities when the image is an asset collection
- RAC-435: Fix fatal error for user that migrate from 4.0 with product values format that doesn't correspond to expected format
- RAC-449: Fix invalid processed item when remove attribute
- PIM-9610: Force displaying years with 4 digits in dates for every locale

## New features

- MET-197: Add possibility to define that an association type is two way & automatically create inversed association when association type is two way
- MET-14: Measurements (or metrics) are now stored in database
- AOB-277: Add an acl to allow a role member to view all job executions in last job execution grids, job tracker and last operations widget.
- RAC-54: Add a new type of associations: Association with quantity
- RAC-123: Add possibility to export product/product model with labels instead of code
- RAC-271: Add possibility to declare jobs as stoppable and stop them from the UI
- RAC-277: Add job progress and remaining time in the UI
- CPM-93: Add a default value for Yes/No attributes; this default value is applied when creating a new product or product model
- PM2020-9: Convert a variant to a simple product

## Improvements

- CLOUD-1959: Use cloud-deployer 2.2 and terraform 0.12.25
- PIM-9306: Enhance catalog volume monitoring count queries for large datasets
- API-1140: Be able to get attributes searching by a list of attribute codes
- API-1225: Be able to get attributes searching by updated date
- API-1226: Be able to get attributes searching by a list of attribute types
- PIM-9368: Allow minimum translation progress of 70% instead of 80%
- PIM-9398: Add a primary key on connection table
- PIM-9371: Disable save button when user creation form is not ready to submit
- RAC-178: When launching a job, the notification contains a link to the job status
- PIM-9485: Change ACL name “Remove a product model” to “Remove a product model (including children)”
- BH-138: clear Locale cache on save
- RAC-393: Improve attribute removal management
- CXP-493: Do not save products when they were not actually updated. In order to do so, the product now returns copies of
  its collections (values, categories, groups, associations and quantified associations). Practically, this means that such a collection cannot be directly
  updated "from outside" anymore (e.g: `$product->getCategories()->add($category)` **won't update the product anymore**,
  you should now use `$product->addCategory($category)` to achieve it)
- CXP-544: Do not save product models when they were not actually updated. As for products, the product model
  will now return copies of its collections (values, categories, associations and quantified associations)

# Technical Improvements

- TIP-1233: Upgrade to php7.4
- CPM-38: Upgrade Symfony to 4.4.15
- TIP-152: Upgrade Mysql to 8.0.22
- BH-286: Up ElasticSearch to 7.10.1
- CPM-33: Upgrade node to 12.19
- CPM-33: Upgrade npm to 6.14
- PIM-9452: Add a command to update the ElasticSearch indexes max fields limit
- RAC-444: Improve jobs logs

## Classes

## BC breaks

- API-1140: Change $criteria format from `Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\AttributeRepository`
  the new format is `[property: [['operator' => (string), 'value' => (mixed)]]]`.

### Codebase

- Change constructor of `Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader` to
  - add `Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelController` to
  - add `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface $productEditDataFilter`
- Change constructor of `\Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi\ProductController` to
  - add `Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts $getConnectorProductsWithOptions`
  - add `Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher`
  - add `GetProductsWithQualityScoresInterface $getProductsWithQualityScores`
  - add `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface $removeParent`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductController` to
  - add `Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface $productEditDataFilter`
  - add `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface $removeParent`
- Change constructor of `Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetricValidator` to
  - remove `array $measures`
  - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $provider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\MeasureFamilyController` to
  - remove `array $measures`
  - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $legacyMeasurementProvider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Controller\MeasuresController` to
  - remove `array $measures`
  - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $provider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter` to
  - remove `array $config`
  - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $provider`
- Change constructor of `Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager` to
  - remove `array $config`
  - add `Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider $legacyMeasurementProvider`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter` to
  - remove `Akeneo\Tool\Component\Localization\TranslatorProxy $translatorProxy`
  - add `Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface $measurementFamilyRepository`
  - add `Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository $baseCachedObjectRepository`
  - add `Psr\Log\LoggerInterface $logger`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\GroupNormalizer` to
  - add `Akeneo\Pim\Enrichment\Component\Product\Query\GetGroupProductIdentifiers`
- Change constructor of `Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute` to
  - add `(string) $defaultMetricUnit`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor` to add `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult $result`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid\FetchProductAndProductModelRows` to add `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetFactory $productAndProductsModelDocumentTypeFacetFactory`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows` to
  - add `?int $totalProductCount`
  - add `?int $totalProductModelCount`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderWithSearchAggregatorFactory` to make not nullable the third parameter `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator $searchAggregator`
- Change `Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager` to remove method `setMeasureConfig(array $config)`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\DependencyInjection\Configuration`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\AreaFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\BinaryFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\CaseBoxFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\DecibelFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\DurationFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\ElectricChargeFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\EnergyFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\FrequencyFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\IntensityFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\LengthFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\PowerFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\PressureFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\ResistanceFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\SpeedFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\TemperatureFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\VoltageFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\VolumeFamilyInterface`
- Remove `Akeneo\Tool\Bundle\MeasureBundle\Family\WeightFamilyInterface`
- Rename `Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException` as `Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException`
- Rename `Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownMeasureException` as `Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\RefreshProductCommand` to
  - replace `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface $productSaver` by `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $productSaver`
  - replace `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface $productModelSaver` by `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $productModelSaver`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\RemoveCompletenessForChannelAndLocaleCommand` to
  - replace `Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface $channelSaver` by `Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $channelSaver`
- Add `getChannels()` and `getLabel()` methods in `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface` interface
- Change `addFieldSorter()` method of `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface` to return `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface`
- The `Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface` interface now also extends `Doctrine\Common\Persistence\ObjectRepository` interface
- Rename the `$objectFilter` property in `Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\CategoryController` to `$collectionFilter`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\SqlGetConnectorProducts` to replace `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $attributeRepository` by `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface $attributeRepository`
- Change `Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface` to use `Akeneo\UserManagement\Component\Model\UserInterface` instead of `Symfony\Component\Security\Core\User\UserInterface`
- Change `Akeneo\Pim\Enrichment\Component\Product\Connector\Step\MassEditStep::setCleaner()` to take `Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit\TemporaryFileCleaner $cleaner` as first argument instead of `Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface $cleaner`
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductNormalizer::normalizeAssociations()` to make the first argument not optional
- Change `Akeneo\Pim\Enrichment\Component\Product\Model\Group::getTranslation()` to return null or an instance of `Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslationInterface`
- Change `Akeneo\Pim\Enrichment\Component\Category\Model\Category::getTranslation()` to return null or an instance of `Akeneo\Pim\Enrichment\Component\Category\Model\CategoryTranslationInterface`
- Change `Akeneo\Pim\Enrichment\Component\Comment\Normalizer\Standard\CommentNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\CollectionNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product\ValueNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductModelNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\ProductNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeOptionNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Change `Akeneo\Pim\Structure\Component\Normalizer\InternalApi\AttributeOptionValueCollectionNormalizer` to implement `Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface` instead of `Symfony\Component\Serializer\SerializerAwareInterface`. That means:
  - the `setSerializer()` method and the `$serializer` property are removed
  - the `setNormalizer()` method and the `$normalizer` property are added
- Remove `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ViolationNormalizer` class, it is replaced by `Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ConstraintViolationNormalizer`
- Change `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface` to add `getId()` and `getIdentifier()` methods
- Change constructor of `Akeneo\Pim\Structure\Bundle\Controller\InternalApi\AttributeGroupController` to replace `Doctrine\ORM\EntityRepository $attributeGroupRepo` by `Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface $attributeGroupRepo`
- Change `Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface` interface to add `getWithVariants()`
- Change constructor of `Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeGroup\Sql\FindAttributeCodesForAttributeGroup` to replace `Doctrine\DBAL\Driver\Connection $connection` by `Doctrine\DBAL\Connection $connection`
- Update `Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface` to
  - remove the `setFamilyId()` method
  - extend the new `Akeneo\Tool\Component\StorageUtils\Model\StateUpdatedAware` interface (with `isDirty()` and `cleanup()` methods)
- Update `Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface` to extend the new `Akeneo\Tool\Component\StorageUtils\Model\StateUpdatedAware` interface (with `isDirty()` and `cleanup()` methods)
- Update `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct` to
  - remove the `setFamilyId()` method
  - remove the `$categoryIds` public property and the `$familyId` and `$groupIds` protected properties
  - add `isDirty()` and `cleanup()` methods
- Change the `Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface` to:
  - remove the `findDatagridViewByAlias()` method
  - rename the `getDatagridViewTypeByUser()` method to `getDatagridViewAliasesByUser()` and add type hint on the return (array)
  - add type hint on the return of the `findDatagridViewBySearch()` method (`Doctrine\Common\Collections\Collection`)
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Job\DeleteProductsAndProductModelsTasklet` to
  - add `Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface $jobRepository`
- Update `Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel` to add `isDirty()` and `cleanup()` methods
- Move `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\WritableDirectory` to `Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory`
- Move `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\WritableDirectoryValidator` to `Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectoryValidator`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Command\CleanRemovedAttributesFromProductAndProductModelCommand` to

  - add `\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher`
  - remove `\Akeneo\Pim\Enrichment\Component\Product\ValuesRemover\CleanValuesOfRemovedAttributesInterface $cleanValuesOfRemovedAttributes`
  - add `\Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface $jobLauncher`
  - add `\Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface $jobInstanceRepository`
  - add `\Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface $countProductsWithRemovedAttribute`
  - add `\Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute`
  - add `\Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface $countProductsAndProductModelsWithInheritedRemovedAttribute`
  - add `\Symfony\Component\Routing\RouterInterface $router`
  - add `string $pimUrl`

- Change the `Oro\Bundle\PimDataGridBundle\Controller\ProductExportController` class to remove the `getRequest()` method
- Change signature of `createInversedAssociation()` from `Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface`
  - remove `AssociationInterface $association`
  - add `string $associationTypeCode` and `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface $associatedEntity`
- Change signature of `removeInversedAssociation()` from `Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface`
  - remove `AssociationInterface $association`
  - add `string $associationTypeCode` and `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface $associatedEntity`
- Update `Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface` interface:
  - Remove method `setAssociations()`
  - Remove method `getAssociationForType()`
  - Remove method `getAssociationForTypeCode()`
  - Add method `hasAssociationForTypeCode()`
  - Add method `addAssociatedProduct()`
  - Add method `removeAssociatedProduct()`
  - Add method `getAssociatedProducts()`
  - Add method `addAssociatedProductModel()`
  - Add method `removeAssociatedProductModel()`
  - Add method `getAssociatedProductModels()`
  - Add method `addAssociatedGroup()`
  - Add method `removeAssociatedGroup()`
  - Add method `getAssociatedGroups()`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder`:
  - add argument `Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface $associationTypeRepository`
  - add argument `Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field\AssociationFieldClearer`: add argument `Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AssociationFieldSetter`: add argument `Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface $associationTypeRepository`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory` to
  - add `Psr\Log\LoggerInterface $logger`
- Move `Akeneo\Channel\Component\Query\GetChannelCodeWithLocaleCodesInterface` to `Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ImmutableVariantAxesValues`
- Remove `Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ImmutableVariantAxesValuesValidator`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\RemoveParentInterface $removeParent`
- Change constructor of `Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui\JobTrackerController` to
  - add `Psr\Log\LoggerInterface $logger`

### CLI commands

The following CLI commands have been deleted:

### Services

- Update `pim_catalog.validator.constraint.valid_metric` to use `akeneo_measure.provider.measurement_provider`
- Update `akeneo_measure.measure_converter` to use `akeneo_measure.provider.measurement_provider`
- Update `akeneo_measure.manager` to use `akeneo_measure.provider.measurement_provider`
- Update `akeneo_measure.controller.rest.measures` to use `akeneo_measure.provider.measurement_provider`
- Update `legacy_pim_api.controller.measure_family` to use `akeneo_measure.provider.measurement_provider`
- Rename `pim_api.controller.measure_family` to `legacy_pim_api.controller.measure_family`
- Remove parameter `akeneo_measure.measures_config`
