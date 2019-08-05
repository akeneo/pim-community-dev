# 3.1.x

## Bug fixes

- PIM-8603: Sort attribute columns by label if no order is defined
- PIM-8600: Fix import filepath tooltip
- PIM-8576: Fix attribute options in case of very long strings

# 3.1.17 (2019-07-30)

## Bug fixes

- PIM-8577: Convert dates to user timezone in the dashboard's last operations widget
- PIM-8557: Remove empty headers when building the error reporting file
- PIM-8597: Fix display of long attribute names in the filter menu

# 3.1.16 (2019-07-22)

## Bug fixes:

- PIM-8570: Fix category tree display

# 3.1.15 (2019-07-19)

## Bug fixes

- PIM-8542: Fix counter on categories when deleting a product on the grid
- PIM-8564: fixes link dialog rendering for wysiwyg editors on product edition page

# 3.1.14 (2019-07-18)

## Bug fixes

- PIM-8540: Display option label in product PDF
- PIM-8549: Fix filter caret invisible when option label is too long
- PIM-8560: Fix maximum height of categories tree to be able to scroll

# 3.1.13 (2019-07-16)

# 3.1.12 (2019-07-15)

# 3.1.11 (2019-07-05)

## Bug fixes

- PIM-8488: Fix wrapping of inline filters on records

# 3.1.10 (2019-07-04)

# 3.1.9 (2019-07-02)

## Bug fixes

- PIM-8481: Fix space between long labels and buttons in simple selects

# 3.1.8 (2019-06-28)

## Bug fixes

- PIM-8475: Fix permission of sorting attribute groups

# 3.1.7 (2019-06-26)

## Bug fixes

- PIM-8428: PIM displays pim_common.code on grids
- PIM-8447: Fix grids thumbnails display
- PIM-8467: Fix warning counts in case of failed jobs in last operations widget

# 3.1.6 (2019-06-11)

## Bug fixes

- PIM-8415: back-port #10002 to handle uploads on a multi-frontend saas instance
- PIM-8419: Render wysiwig in compare/translate view

## BC breaks

 - Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
 - Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor` to add `Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer`
 - Change constructor of `Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\MediaAttributeSetter` to remove `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`

# 3.1.5 (2019-05-28)

# 3.1.4 (2019-05-27)

## Bug fixes

- PIM-8374: Fix timeout when launching the completeness purge command

# 3.1.3 (2019-05-21)

# 3.1.2 (2019-05-03)

# 3.1.1 (2019-05-02)

# 3.1.0

Release of the 3.1.0

# 3.1.0-BETA1

## Bug fixes

## Technical improvement

- DAPI-19: Update `Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductPropertiesNormalizer` to accept optional normalizers
- TIP-1149: Update the warning limits in the Catalog volume monitoring screen

## BC breaks

- Twig extension `get_attribute_label_from_code` has been removed with the class `Akeneo\Pim\Structure\Bundle\Twig\AttributeExtension`.
- `Akeneo\Platform\Bundle\DashboardBundle\Widget\LastOperationsWidget` moved to `Akeneo\Platform\Bundle\ImportExportBundle\Widget\LastOperationsWidget`
- Method `public function getLastOperationsData()` removed from `Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository`.
    Use `Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperations->execute()` instead.
- `Akeneo\Platform\Bundle\ImportExportBundle\Manager\JobExecutionManager` removed.
    Use `Akeneo\Platform\Bundle\ImportExportBundle\Widget\LastOperationsFetcher` instead.
- `Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface` does not extend `Akeneo\Tool\Component\Localization\Model\LocalizableInterface` nor `Akeneo\Pim\Enrichment\Component\Product\Model\ScopableInterface` anymore
- methods `getScope()`, `setScope()`, `getLocale()` and `setLocale()` were removed from `Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct`
- class `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ScopableSubscriber` and its associated service definition `pim_catalog.event_subscriber.scopable` were removed
- Interface `Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface` added methods `setRatio()` , `setMissingCount()` and `setRequiredCount()`
- Class `Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\CompletenessRemover` `bulkDetacher` parameter is now mandatory.
- Removed `objectDetacher` parameter from `Akeneo\Pim\Structure\Component\Model\FamilyInterface\SaveFamilyVariantOnFamilyUpdateSubscriber` constructor.
- Move `Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid\FromSizeIdentifierResultCursorFactory` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\FromSizeIdentifierResultCursorFactory`
- Move `Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid\IdentifierResultCursor` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor`
- Move `Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductGrid\SearchAfterSizeIdentifierResultCursorFactory` to `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchAfterSizeIdentifierResultCursorFactory`
