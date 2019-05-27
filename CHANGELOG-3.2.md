# 3.2.x

## Bug fixes

 - PIM-8270: Update export jobs after a change on a channel category

## Technical improvement

- DAPI-242: Improve queue to consume specific jobs

## BC breaks

 - Service `pim_catalog.saver.channel` class has been changed to `Akeneo\Channel\Bundle\Storage\Orm\ChannelSaver`.
 - Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
 - Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
 - Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter` to remove `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
 - `Akeneo\Tool\Component\Connector\Archiver\AbstractInvalidItemWriter` now requires a `getFilename()` method to be implemented.
