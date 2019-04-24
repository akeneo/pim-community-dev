# 3.x

## Improvements

- DAPI-19: Add filter on subscribed/unsubscribed products to Franklin Insights in the product grid
- DAPI-20: Display Franklin Insights subscription as a column of the product grid
- PIM-8181: Reference entities - As Julia, I would like to display the options in the records grid
- PIM-8182: Reference entities - As Julia, I would like to filter the options in the records grid
- PIM-8183: Reference entities - As Julia, I would like to display the reference entity links in the records grid
- PIM-8184: Reference entities - As Julia, I would like to filter the reference entity links in the records grid
- PIM-8281: Reference entities - As Julia, I would like to easily empty a filter in the records grid
- PIM-8292: Reference entities - As Julia, I would like to view the completeness of a record in the records selector
- TIP-1149: Update the warning limits in the Catalog volume monitoring screen

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
- TIP-1084: The service `pimee_security.product_grid.query.fetch_user_rights_on_product` has been renamed into `akeneo.pim.permission.product.query.fetch_user_rights_on_product`
- TIP-1084: The class `UserRightsOnProduct` is constructed with a new arguments `numberOfViewableCategories`
- TIP-1084: The class `FetchUserRightsOnProduct` has been moved from `Akeneo\Pim\Permission\Bundle\Persistence\Sql\DatagridProductRight` to `Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product`
- TIP-1085: The service `pimee_security.product_grid.query.fetch_user_rights_on_product_model` has been renamed into `akeneo.pim.permission.product.query.fetch_user_rights_on_product_model`
- TIP-1085: The class `UserRightsOnProductModel` is constructed with a new argument `numberOfViewableCategories`
- TIP-1085: The class `FetchUserRightsOnProductModel` has been moved from `Akeneo\Pim\Permission\Bundle\Persistence\Sql\DatagridProductRight` to `Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel`
