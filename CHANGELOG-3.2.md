# 3.2.x

# 3.2.10 (2019-10-02)

## Bug fixes

- PIM-8752: Fix reference entities filter box popup display
- PIM-8837: Fix issue on the draft creation, when an attribute code is a numerical value
- PIM-8834: Fix asset title page

# 3.2.9 (2019-09-23)

- PIM-8781: Fix catalog volume monitoring and system information showing unused data
- DAPI-420: Add url for the "Read more" link on the option mapping screen when it is empty

# 3.2.8 (2019-09-17)

## Bug fixes

- PIM-8596: add translation for the Attribute type filter `pim_catalog_asset_collection`
- PIM-8719: Update Mink Selenium driver
- PIM-8734: Change label to "Ecommerce" for default channel in minimal catalog
- PIM-8712: Add and use a dedicated filesystem to upload assets
- PIM-8752: Fix reference entities filter box popup display
- PIM-8665: Adds a new log handler to save SAML logs in DB (not enabled by default) and properly cleanup logs archive after download

# 3.2.7 (2019-08-27)

# 3.2.6 (2019-08-22)

# 3.2.5 (2019-08-19)

## Bug fixes

- PIM-8654: Display option label instead of code in product PDF

# 3.2.4 (2019-08-14)

# 3.2.3 (2019-08-13)

## Bug fixes

- PIM-8594: Fix two many spaces in the reference entities sub menu navigation

# 3.2.2 (2019-08-01)

# 3.2.1 (2019-07-31)

# 3.2.0 (2019-07-24)

# 3.2.0-BETA3 (2019-07-22)

# 3.2.0-BETA2 (2019-07-22)

# 3.2.0-BETA1 (2019-07-19)

## Features

- Reference entities: Addition of the number attribute type
- Reference entities: Display the products linked to a record
- Franklin insights: Perfect match to ease the mapping of the attributes
- Franklin insights: Suggestion of attribute creation to ease the mapping of the attributes
- Franklin insights: Suggestion of attribute attachment to a family to ease the mapping of the attributes
- Franklin insights: Progress bar to follow the attributes mapping process
- Workflow: New filter on draft status in the product grid
- Workflow: Send for approval drafts directly in the product sequential edition
- Workflow: Send for approval drafts directly in the bulk action
- Performance enhancements: Export products with the API way faster than before
- API: Add the family code in the product model format
- API: New filter to retrieve the variant products of a given product model

## Improvements

- DAPI-138: Always display the button _Send for approval_ on the PEF as a shortcut to save and send a draft for approval
- DAPI-262: Add a create attribute action on the Franklin Insights mapping screen.
- DAPI-271: Always display suggested value and attribute type on the Franklin Insights mapping screen.
- DAPI-270: Add a progress bar on the attribute mapping screen of Franklin Insights.
- DAPI-137: Add possibility to filter by draft status in the product grid

## Bug fixes

- DAPI-366: Fix icon used for Franklin-Insights notifications 
- GITHUB-10083: Fix proposal datagrid render when deleting values

## Technical improvement

## BC breaks

- Change constructor of `Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor` to add `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface` and `Akeneo\Tool\Component\FileStorage\File\FileStorerInterface`
- The ValueCollection interface has been renamed into WriteValueCollectionInterface please apply `find ./src/ -type f -print0 | xargs -0 sed -i 's/ValueCollectionInterface/WriteValueCollectionInterface/g`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProductProposalNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory` and `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ProductModelProposalNormalizer` to add `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory` and `Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory` to replace the parameters `accessLevel` and `categoryAccessRepository` by an implementation of `Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetAllGrantedCategoryCodes`
- Remove method `getGrantedCategoryQB` from `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository`
- Remove method `getGrantedCategoryCodes` from `Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository` in favor of `Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetAllGrantedCategoryCodes` implementations
- Change constructor of `Akeneo\Pim\Permission\Bundle\EventSubscriber\AddDefaultPermissionsSubscriber` to add `Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager`
