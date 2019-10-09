# 3.2.x

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
