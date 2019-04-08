# 3.x

## Improvements

- DAPI-19: Add filter on subscribed/unsubscribed products to Franklin Insights in the product grid
- DAPI-20: Display Franklin Insights subscription as a column of the product grid
- PIM-8181: As Julia, I would like to display the options in the records grid

## Bug fixes

## Technical improvement

## BC breaks

- DAPI-58: Add `Oro\Bundle\PimDataGridBundle\Query\ListAttributesUseableInProductGrid`,
  `Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface` and
  `Oro\Bundle\PimDataGridBundle\Extension\Filter\FilterExtension` to
  `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest\ProductDraftController`
- methods `getScope()` `setScope()`, `getLocale()` and `setLocale()` were removed from `Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct`
- Change constructor of `Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener\CatalogUpdatesSubscriber`. Add argument `Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext`
- Renamed class `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router\ProxyProductRouter` to `ProxyRouter`.
- Changed the order of the parameters for class constructor `Akeneo\Pim\Permission\Bundle\Pdf\ProductPdfRenderer` and the parameter `assetRepository` is now mandatory.
