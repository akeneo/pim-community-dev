# 3.2.x

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
