# 3.2.x

## Bug fixes

- PIM-8270: Update export jobs after a change on a channel category

## BC Breaks

- The `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface` interface has been renamed into `Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollectionInterface`
- Rename `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AncestorFilter` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AncestorIdFilter`
- DAPI-137: Fix the PQB to not aggregate results when there is a filter on id
- Remove class `Akeneo\Pim\Permission\Bundle\EventSubscriber\AddDefaultPermissionsSubscriber` and its associated service definition

## Enhancements

- The search on option.codes in ES is now case insensitive









































## Technical improvement

- DAPI-242: Improve queue to consume specific jobs
- TIP-1117: For security reasons, "admin" user is no longer part of the minimal catalog
- TIP-1117: `pim:user:create` command now has a non interactive mode











































## Enhancements

- TIP-1144: External API - add `family` into the product model format








































## BC breaks

- Service `pim_catalog.saver.channel` class has been changed to `Akeneo\Channel\Bundle\Storage\Orm\ChannelSaver`.
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter` to remove `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
- `Akeneo\Tool\Component\Connector\Archiver\AbstractInvalidItemWriter` now requires a `getFilename()` method to be implemented.
- The ValueCollectionInterface as been removed please apply `sed 's/ValueCollectionInterface/WriteValueCollection/g` (it also rename the ValueCollection)
- The ValueCollectionFactoryInterface has been removed please apply `sed 's/ValueCollectionFactoryInterface/ValueCollectionFactory/g`
- Change constructor of `Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher` to add `Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler`
- Change constructor of `Akeneo\Platform\Bundle\ImportExportBundle\Controller\JobExecutionController` to add `League\Flysystem\FilesystemInterface`
- Make method `getRealPath` of `Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler` private
- Change constructor of `Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeTypeForOptionValidator` to add array `$upportedAttributeTypes`
- Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Job\DeleteProductsAndProductModelsTasklet` to add `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CountProductModelsAndChildrenProductModelsInterface` and `Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountVariantProductsInterface`





























































