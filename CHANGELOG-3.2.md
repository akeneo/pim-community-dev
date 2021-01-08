# 3.2.x

## Bug fixes

- PIM-9614: Fix missing overlay when the announcements panel is open

## Improvement:

- PIM-9483: Users with the ACL "View the associations of a product" can view associations of products, even if they are not owner

# 3.2.79 (2020-12-17)

## Improvement

- PIM-9599: Fix @babel/types dependency to ^7.11.5

# 3.2.78 (2020-12-02)

## Bug fixes

- RAC-384: Fix fatal error when an attribute is removed then re-created with the same code but another type.

# 3.2.77 (2020-11-30)

## Bug fixes

- [Backport] Fix fatal error on display product model associations when they have more than 25 products associated
- PIM-9578: Revert change on millibar conversion operation
- PIM-9580: Fix conversion operation for ATM, PSI, TORR & MMHG

# 3.2.76 (2020-10-22)

## Features:

- GITHUB-APD-227: add the PIM communication channel

Four frontend dependencies are required for the backport of this feature (@types/react-dom, react-markdown, styled-components, types/styled-components). Therefore, it will add these dependencies in the file`package.json` of your project automatically when executing `composer update` or `composer install`.  

Please commit changes of the file `package.json` in your CVS.

## Bug fixes:

- PIM-9523: Fix pressure measure multiplicator error

# 3.2.75 (2020-10-14)

## Bug fixes:

- GITHUB-12899: Fix error shown when importing product models with the same code

# 3.2.74 (2020-10-09)

# 3.2.73 (2020-10-09)

## Bug fixes:

- PIM-9501: [Backport] PIM-9494: Fix the performances of attribute-select-filter on long lists of AttributeOptions

# 3.2.72 (2020-09-30)

# 3.2.71 (2020-09-21)

## Bug fixes:

- AOB-1080: Fix pim/onboarder logo submenu display under collapsed column
- PIM-9450: Fix error during family import, compute products/product models doesn't work when products/product models are skipped
- PIM-9453: Use specific query to fetch product identifiers linked to a group.

# 3.2.70 (2020-09-10)

# 3.2.69 (2020-09-07)

## Bug fixes:

-PIM-9101: Be able to create a family, a family variant, a group type, an association type named "rest".

# 3.2.68 (2020-09-03)

## Bug fixes:

- PIM-9427: Update datagrid selection count when clearing filter
- PIM-9402: Add new ES filters for EMPTY operator

# 3.2.67 (2020-08-07)

## Bug fixes:

- PIM-9380: Parts of the PIM are not translatable on Crowdin

# 3.2.66 (2020-08-04)

# 3.2.65 (2020-07-20)

# 3.2.64 (2020-07-16)

# 3.2.63 (2020-07-07)

# 3.2.62 (2020-07-02)

# 3.2.61 (2020-06-29)

## Bug fixes

- PIM-9323: Fix PDF export for Product Model values

# 3.2.60 (2020-06-22)

## Bug fixes

- PIM-9314: Reset scroll position when switching attribute group in the PEF
- PIM-9084: Filter locale specific attributes in exports when the value's locale and the export profile's locale are different

# 3.2.59 (2020-06-15)

## Bug fixes

- PIM-9203: Box shadow appearing on category selector in product grid
- PIM-9282: Make calling attribute options via API case insensitive

# 3.2.58 (2020-06-01)

# 3.2.57 (2020-05-28)

## Improvement

- PIM-9268: Add ko_KR to locales default list.

## Bug fixes

- PIM-9255: Refresh completeness of associated products in grid when channel is switched
- PIM-9262: Keep image rotation on images and assets thumbnails

# 3.2.56 (2020-05-25)

## Bug fixes

- AOB-953: Fix `ComputeFamilyVariantStructureChanges` constraint by accepting array of several elements

## Improvement

- PIM-9165: Improve message of `akeneo:elasticsearch:reset-indexes` command. Now, all commands available to re-index entities are shown.

# 3.2.55 (2020-05-14)

## Bug fixes

- PIM-9245: Fix relative JS files path for less compilation
- PIM-9249: Fix channel and locale popin display on product export

# 3.2.54 (2020-05-06)

## Bug fixes

- PIM-9221: Fix performance issue when saving a variant product/product model with a lot of siblings

# 3.2.53 (2020-05-05)

## Bug fixes

- PIM-9222: Fix errors on mass action when the parent filter is set to empty

# 3.2.52 (2020-04-30)

## Bug fixes

- GITHUB-11995: Fixes invalid Jquery generic type (#11995)

# 3.2.51 (2020-04-28)

## Bug fixes

- PIM-9212: Keep vertical scroll position of the attribute form after editing values

# 3.2.50 (2020-04-24)

## Bug fixes:

- PIM-9219: Fix duplicated categories in the product API when present in both Product Model and Variant

# 3.2.49 (2020-04-23)

## Bug fixes

- PIM-9205: Fix PDF not exporting Product Model values
- PIM-9204: Fix error when the user does not have the permission to view the axis attribute

# 3.2.48 (2020-04-21)

# 3.2.47 (2020-04-14)

## Bug fixes

- PIM-9177: Handle the display of a large number of attributes within an attribute group
- PIM-9176: Fix the permission update of attribute groups having a lot of attributes

# 3.2.46 (2020-03-30)

# 3.2.45 (2020-03-20)

## Bug fixes

- PIM-9152: Handle old versioning date format

# 3.2.44 (2020-03-13)

## Bug fixes

- PIM-9148: Fix UI attribute group display in association panel

# 3.2.43 (2020-03-11)

# 3.2.42 (2020-03-04)

## Bug fixes:

- PIM-9111: Fix the variant list when the channel does not support the selected locale

# 3.2.41 (2020-02-26)

## Bug fixes:

- PIM-9108: Fix 'unsaved changes' message wrongly displayed when switching category tree

# 3.2.40 (2020-02-20)

## Bug fixes:

- PIM-9098: keep vertical scroll position of the product form after saving enrichment
- PIM-9103: keep vertical scroll position of the product form after switching the locale or channel

# 3.2.39 (2020-02-17)

## Bug fixes:

- PIM-9095: Fix memory leak during family import

# 3.2.38 (2020-02-12)

## Bug fixes:

- PIM-9089: Fix error when a category is unknown during a product search

# 3.2.37 (2020-02-04)

## Bug fixes:

- PIM-9080: Fix overlapping jQuery.resizable handle

# 3.2.36 (2020-02-03)

## Bug fixes:

- PIM-9075: Fix mass_edit_rule key translation
- PIM-9071: Fix "does not contain" filter on SKU in product data grid

# 3.2.35 (2020-01-29)

- PIM-9067: Fix mass action product edit when all rows are selected

# 3.2.34 (2020-01-21)

# 3.2.33 (2020-01-20)

## Bug fixes:

- PIM-9059: Fix catalog locale on user panel & attribute groups attributes

# 3.2.32 (2020-01-17)

## Bug fixes:

- PIM-9056: Fix filters with numeric attribute codes for bulk actions
- PIM-9058: Fix search products API with filters on numeric attribute codes

# 3.2.31 (2020-01-14)

## Bug fixes:

- PIM-9054: Fix the constraint on AttributeOption:code max length
- PIM-9053: Update product label when locale is changed
- PIM-9022: Fix behaviour of the 'Previous' button in product bulk actions modal

# 3.2.30 (2020-01-10)

# 3.2.29 (2020-01-03)

# 3.2.28 (2019-12-31)

## Bug fixes:

- PIM-9043: Do not filter archivable jobs to be able to download all logs
- PIM-9029: Use Catalog locale in variant families datagrid

# 3.2.27 (2019-12-19)

## Bug fixes:

- PIM-9027: Translations missing on metrics
- PIM-9028: Fix error on JSON_EXTRACT where a_image.code is numerical

## Technical improvement

- Update composer dependencies

# 3.2.26 (2019-12-16)

## Bug fixes:

- PIM-9021: Fix input displaying when selecting empty/not empty filter operator

## Technical improvement

- DAPI-691: Add a blacklist option to the CE job queue daemon command

# 3.2.25 (2019-12-11)

## Bug fixes:

- PIM-9020: Add missing attribute group code validation message translation key

# 3.2.24 (2019-12-10)

## Bug fixes:

- PIM-8998: Fix error 500 on asset list & product list when a user has no permission on categories & asset categories
- PIM-9016: Fix error message not translated for attribute code
- PIM-9015: Fix validation message display on attribute group creation failure

# 3.2.23 (2019-12-05)

## Bug fixes:

- PIM-9008: Fix product models API when the filter "completenes" was used with a sub-filter "locale"
- PIM-9005: Fix display of long option labels in the Product Edit Form

# 3.2.22 (2019-12-03)

## Bug fixes:

- PIM-8996: Display an error when a file upload failed
- PIM-8984: Fix css on records dropdowns
- PIM-8988: Remove useless "Remove" button when editing exported attributes (in export profile)

# 3.2.21 (2019-11-22)

- PIM-8995: Fix the completeness widget (dashboard) for channels having no translations

# 3.2.20 (2019-11-20)

# 3.2.19 (2019-11-15)

# 3.2.18 (2019-11-13)

## Bug fixes:

- PIM-8943: Display validation messages on family translations
- PIM-8953: User without "list users" permission can not access to other user pages
- PIM-6902: Forbid usage of uppercase in Elasticsearch aliases (see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html)
- PIM-8961: Fix design of 403 error page

# 3.2.17 (2019-10-30)

## Bug fixes:

- PIM-8925: Fix PDF export when there is no option for a given locale for a select attribute

# 3.2.16 (2019-10-24)

## Bug fixes:

- GITHUB-10955: Remove database prefix in queries

# 3.2.15 (2019-10-24)

## Improvements

- PIM-8909: Remove css.map links

## Bug fixes:

- PIM-8908: Fix asset default image always showing

# 3.2.14 (2019-10-22)

## Features

- AOB-661: allow prefix of ES document `_id`

## Bug fixes:

- PIM-8893: update dompdf because the pdf export fail on some products
- PIM-7963: Fix datepicker width not adapting to the dropdown

# 3.2.13 (2019-10-18)

## Bug fixes:

- PIM-8773: Fix logout after opening a select2 dropdown
- PIM-8879: Validate attribute options existence

# 3.2.12 (2019-10-08)

# 3.2.11 (2019-10-07)

## Bug fixes:

- PIM-8838: Force display of label and image in gallery mode even if they are not in the column list
- PIM-8820: Avoid multiple refresh of the product grid on product delete
- PIM-8838: Force display of identifier, label and image in gallery mode even if they are not in the column list

# 3.2.10 (2019-10-02)

## Bug fixes:

- PIM-8820: Avoid multiple refresh of the product grid on product delete
- PIM-8736: Fix error message on channel deletion

# 3.2.9 (2019-09-23)

## Bug fixes:

- PIM-8767: Fix user security token check
- PIM-8787: Fix API search-after - missing search_scope in first, next, previous, current links

# 3.2.8 (2019-09-17)

## Bug fixes:

- PIM-8719: Fix Mink Selenium dependency
- PIM-8677: Purge all job executions
- PIM-8734: Change label to "Ecommerce" for default channel in minimal catalog
- PIM-8753: Fix pim:versioning:purge command without parameter
- PIM-8750: Fix keyboard navigation with the family selector on the create product form
- PIM-8766: Use Catalog locale for channel labels in the completeness widget

# 3.2.7 (2019-08-27)

## Bug fixes:

- PIM-8655: Fix page title of the categories settings
- PIM-8701: Fix PDF rendering for scopable/localizable simple or multi select attributes

# 3.2.6 (2019-08-22)

## Bug fixes:

- PIM-8663: Fix category tree selector
- PIM-8674: Check date validity when creating a date value
- PIM-8673: Add a fallback to get the mime-type of files loaded without metadata.

# 3.2.5 (2019-08-19)

## Bug fixes

- PIM-8661: API: Fix getting values from a variant product when one of its ancestors has empty values

# 3.2.4 (2019-08-14)

# 3.2.3 (2019-08-13)

## Bug fixes

- PIM-8601: Fix purge of the job execution according to the date of creation and not deletion
- PIM-8583: Add missing translations on role deletion

# 3.2.2 (2019-08-01)

## Bug fixes

- PIM-8595: Fix missing translation (pim_common.code) in attributes list / family list

# 3.2.1 (2019-07-31)

# 3.2.0 (2019-07-24)

# 3.2.0-BETA3 (2019-07-22)

# 3.2.0-BETA2 (2019-07-22)

# 3.2.0-BETA1 (2019-07-19)

## Features

- Performance enhancements: Export products with the API way faster than before
- API: Add the family code in the product model format
- API: New filter to retrieve the variant products of a given product model

## Bug fixes

- PIM-8270: Update export jobs after a change on a channel category

## BC Breaks

- DAPI-137: Fix the PQB to not aggregate results when there is a filter on id
- The `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface` interface has been renamed into `Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollectionInterface`
- Service `pim_catalog.saver.channel` class has been changed to `Akeneo\Channel\Bundle\Storage\Orm\ChannelSaver`.
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter` to remove `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
- `Akeneo\Tool\Component\Connector\Archiver\AbstractInvalidItemWriter` now requires a `getFilename()` method to be implemented.
- The ValueCollectionInterface as been removed. Please directly use the WriteValueCollection class instead.
- The ValueCollectionFactoryInterface has been removed please apply `sed 's/ValueCollectionFactoryInterface/ValueCollectionFactory/g`
- Change constructor of `Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher` to add `Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler`
- Change constructor of `Akeneo\Platform\Bundle\ImportExportBundle\Controller\JobExecutionController` to add `League\Flysystem\FilesystemInterface`
- Make method `getRealPath` of `Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler` private
- Change constructor of `Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeTypeForOptionValidator` to add array `$upportedAttributeTypes`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Job\DeleteProductsAndProductModelsTasklet` to add `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CountProductModelsAndChildrenProductModelsInterface` and `Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountVariantProductsInterface`

- Rename `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AncestorFilter` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AncestorIdFilter`
- Rename `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductModels` to `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\SqlGetConnectorProductModels`
- The following classes and their service definitions have been removed:
  - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\RemoveUserSubscriber`
  - `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductModels`
  - `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductsFromWriteModel`
  - `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetMetadataForProductModel`
  - `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactory`
  - `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface`
  - `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection`
  - `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface`
  - `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetMetadataInterface`
  - `Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadata`
  - `Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadataInterface`

## Enhancements

- The product and product model search on option.codes in ES is now case insensitive
- TIP-1144: External API - add `family` into the product model format

## Technical improvement

- DAPI-242: Improve queue to consume specific jobs
- TIP-1117: For security reasons, "admin" user is no longer part of the minimal catalog
- TIP-1117: `pim:user:create` command now has a non interactive mode
- TIP-1190: Refresh of the ES index is not at wait_for but disabled by default for performance reason
