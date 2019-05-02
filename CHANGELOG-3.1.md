# 3.1.x

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
