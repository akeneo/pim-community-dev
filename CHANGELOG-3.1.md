# 3.1.x

# 3.1.14 (2019-07-18)

## Bug fixes

- PIM-8543: Fix record count after deletion 

# 3.1.13 (2019-07-16)

## Bug fixes

- PIM-8539: Sort record by code when searching by codes

# 3.1.12 (2019-07-15)

## Bug fixes

- PIM-8536: Fix records filters freezing because of select2

# 3.1.11 (2019-07-05)

# 3.1.10 (2019-07-04)

## Bug fixes

- PIM-8447: Fix deformed images in dropdowns and grids

# 3.1.9 (2019-07-02)

## Bug fixes

- PIM-8479: Fix broken select dropdown in reference entity filters

# 3.1.8 (2019-06-28)

## Bug fixes

- PIM-8472: Fix product model creation with a reference entity axis

# 3.1.7 (2019-06-26)

## Bug fixes

- DAPI-9: Fix use of the database name in a SQL query
- PIM-8451: Add pagination for rules grid in attribute edit form
- PIM-8468: Fix link button on the WYSIWYG that was not fully clickable

# 3.1.6 (2019-06-11)

## Bug fixes

- PIM-8415: back-port #10002 to handle uploads on a multi-frontend saas instance

## BC breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor` to add `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` and `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`

# 3.1.5 (2019-05-28)

## Bug fixes

- PIM-8380: Fix API regression on reference entity with a multiple links attribute

# 3.1.4 (2019-05-27)

# 3.1.3 (2019-05-21)

## Bug fixes

- PIM-8337: fix duplicate 'View' type in view selector

# 3.1.2 (2019-05-03)

## Bug fixes

- Fix the migration script `Version_3_1_20190305152628_change_attribute_column_in_franklin_mapping` to correctly remove the foreign key and unique constraints on `pimee_franklin_insights_identifier_mapping`

# 3.1.1 (2019-05-02)

# 3.1.0

Release of the 3.1.0

# 3.1.0-BETA1

## Improvements

- DAPI-19: Franklin Insights - Add filter on subscribed/unsubscribed products to Franklin Insights in the product grid
- DAPI-20: Franklin Insights - Display Franklin Insights subscription as a column of the product grid
- DAPI-37: Franklin Insights - As Julia, I don't want to see the "refresh project completeness" in the process tracker
- DAPI-215: Franklin Insights - As Julia, I'd like unmapped attributes to be displayed first in attribute mapping screen
- DAPI-21: Franklin Insights - As Julia, I'd like the family label to be displayed in the attributes mapping screen
- DAPI-22: Franklin Insights - As Julia, I'd like the family to be always displayed in the attribute mapping screen
- DAPI-36: Franklin Insights - As Julia, it seems weird to see my name in the widget dropdown
- DAPI-136: Proposals - As Julia, I'd like proposal comment popin to be removed for Franklin proposal
- DAPI-58: Proposals - As Julia, I’d like proposal filter to be displayed in one panel
- DAPI-61: Proposals - As Julia, I'd like the proposals grid to load faster
- DAPI-29: Teamwork assistant - As Julia, I'd like to know that the number displayed in the widget is not the final one
- PIM-8181: Reference entities - As Julia, I would like to display the options in the records grid
- PIM-8182: Reference entities - As Julia, I would like to filter the options in the records grid
- PIM-8183: Reference entities - As Julia, I would like to display the reference entity links in the records grid
- PIM-8184: Reference entities - As Julia, I would like to filter the reference entity links in the records grid
- PIM-8281: Reference entities - As Julia, I would like to easily empty a filter in the records grid
- PIM-8292: Reference entities - As Julia, I would like to view the completeness of a record in the records selector
- TIP-1149: Update the warning limits in the Catalog volume monitoring screen

## Bug fixes

- DAPI-206: Franklin Insights - Fix the link of the attribute option mapping modal
- DAPI-135: Teamwork assistant - Fix project creation if there is no filter on scope in datagrid context

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
