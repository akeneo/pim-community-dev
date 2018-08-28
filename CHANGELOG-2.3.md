# 2.3.x

# 2.3.8 (2018-09-14)

## Bug fixes

- PIM-7648: Fix preview of huge images in Product Edit Form
- PIM-7647: Fix completeness filter on the product export builder

# 2.3.7 (2018-09-11)

## Bug fixes

- PIM-7628: Fix the initialization of the product datagrid identifier filter.
- PIM-7594: Fix memory leak in `pim:versioning:purge` command
- PIM-7635: Fix elasticsearch config override
- PIM-7598: Fix locale change on reference data on simple and multi select
- PIM-7484: Search families and family variants regardless of the current locale

## BC breaks

- PIM-7594: Method `Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface::findPotentiallyPurgeableBy` returns now an CursorInterface

## Enhancements

- PIM-7612: Add the media/cache/{filter}/{path} route support in order to handle scalable frontend architecture for media content delivering

## Technical improvements

- PIM-7601: Update Symfony to 3.4.4

# 2.3.6 (2018-09-06)

## Enhancements

- PIM-7610: Add a command to create users

## Bug fixes

- PIM-7600: Change the default return value of ResetIndexesCommand to true to allow the --no-interaction parameter.
- PIM-7572: Cross to remove associations displayed at PV level whereas association is done at PM level
- PIM-7618: Hide the "Process tracker" link in the Dashboard if the user does not have the permission 
- PIM-7626: Fix attribute groups order in the product grid's column configurator
- PIM-7631: Fix API filter product and product model on date with between operator
- PIM-7613: Fix translations of boolean attributes
- PIM-7609: Handle 'empty' and 'not empty' filter types in string filter

## BC breaks

- Change the constructor of `Pim\Bundle\AnalyticsBundle\DataCollector\DBDataCollector.php` to add `Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxProductValuesPerFamily` as a new argument
- Change the constructor of `Pim\Bundle\AnalyticsBundle\DataCollector\AttributeDataCollector.php` to add `Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\CountUseableAsGridFilterAttributes`, `Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxLocalizableAttributesPerFamily`, `Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxScopableAttributesPerFamily` and `Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxLocalizableAndScopableAttributesPerFamily` as new arguments

# 2.3.5 (2018-08-22)

## Bug fixes

- PIM-7580: Fixes the search on categories with product models
- PIM-7573: Fix "nesting level too deep" error during family import
- PIM-7562: Fix API filter product on status and groups
- PIM-7571: Fix job instance validation in case of attribute deletion
- PIM-7542: Fix completeness filter on edit product group page
- PIM-7589: Fix job `compute_product_models_descendants` launched too many times
- PIM-7587: Fix the preview generation configuration with imagine
- PIM-7414: Fix localisable assets used as main image for family and added to product, break the product form

## BC breaks

- PIM-7414: Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add `Pim\Bundle\CatalogBundle\Context\CatalogContext` as a new argument.

# 2.3.4 (2018-08-08)

## Bug fixes

- PIM-7537: Fix console error in password reset form
- PIM-7552: Fix SKU filter display bug on group grid
- PIM-7543: Forbid usage of 'label' code for attributes to prevent UI bugs
- PIM-7545: Redirect to login page when user is not authenticated anymore
- PIM-7569: Fix limit of 20 results for view selector

# 2.3.3 (2018-08-01)

## Bug fixes

- PIM-7529: Fix error when a tree is removed
- PIM-7536: Fix tool-tip error
- GITHUB-8550: Exclude more folders in typescript to improve build time
- GITHUB-8578: Fixed wrong labels displayed in the "active/inactive" filter of user grid" (Thanks [oliverde8](https://github.com/oliverde8)!)
- PIM-7551: Fix issue on product model import when using custom column headers
- PIM-7541: Fix issue on filtered search on created and updated product and product model properties. Date must be instanciated on server timezone.

# 2.3.2 (2018-07-24)

## Bug fixes

- PIM-7528: Fix Product and Product Model date attribute rendering in history panel, no timezone needed.
- PIM-7488: use catalog locale for attributes list in attribute groups
- PIM-7518: fix memory leak in channel/locale clean command
- PIM-7517: fix the product models export filter on identifier
- PIM-7476: fix family select2 to have the right limit
- PIM-7516: fix metric default value on product edit form

## Migration

**IMPORTANT** Please run the doctrine migrations command to fix the product models export profiles : `bin/console doctrine:migrations:migrate --env=prod`


# 2.3.1 (2018-07-04)

## Enhancements

- PIM-7465: Set form data entity into field context.

# 2.3.0 (2018-06-25)

# 2.3.0-BETA1 (2018-06-21)

## Monitor your catalog volume

- PIM-7209: As John, I want to be able to get info about my catalog volume.

## Improve Julia's experience

- PIM-7347: Improve the calculation of the completeness for locale specific attributes.
- PIM-7345: Remove the "is empty" operator for sku.

# 2.3.0-ALPHA2 (2018-06-07)

## Better manage products with variants

- PIM-6897: As Julia, I would like to update the family variant labels from the UI
- PIM-7302: As Julia, I am not able to delete an attribute option if it's use as variant axis.
- PIM-7330: Improve validation message in case of product model or variant product axis values duplication
- PIM-7326: Create a version when the parent of a variant product or a sub product model is changed.
- PIM-6784: Improve the product grid search with categories for products with variants
- PIM-7308: As Julia, I would like to bulk add product models associations if product models are selected.
- PIM-7293: As Julia, I would like to export variant products associations with their parent associations.
- PIM-7001: Don't display remove button on an association if it comes from inheritance
- PIM-7390: In the Product Edit Form, we now keep the context of attributes filter (missing, all, level specific...)
- PIM-7430: Mass associate generate new product version
- PIM-7298: As Julia, I would like to change the parent of a sub product model by import.
- PIM-6350: As Julia, I would like to change the parent of a variant product/sub product model from the UI.

## Technical improvements

- PIM-7302: Add a 'family_variant' filter in the Product Query Builder with operators 'IN', 'NOT IN', 'EMPTY' and 'NOT EMPTY'.
- PIM-7324: Rework structure version provider to better handle cache invalidation.

## BC Breaks

- Remove public constant `Pim\Component\Catalog\Validator\Constraints\UniqueVariantAxis::DUPLICATE_VALUE_IN_SIBLING`
- Change the method signature of `Pim\Component\Catalog\Validator\UniqueAxesCombinationSet::addCombination`, this method does not return anything anymore, but can throw `AlreadyExistingAxisValueCombinationException`
- Add method `generateMissingForProducts` to `Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface`
- Add a new public method `findProductModelsForFamilyVariant` to `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface`
- Change signature of `Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver` constructor to add `Akeneo\Component\StorageUtils\Indexer\IndexerInterface`

# 2.3.0-ALPHA1 (2018-04-27)

## Better manage products with variants

- PIM-6795: As Julia, I would like to display only the current level attributes
- PIM-7284: Be able to bulk change the status of children products if product models are selected
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import
- PIM-7000: As Julia, I would like to manage associations for products models from the UI
- PIM-6991: As Julia, I would like to export product models associations
- PIM-7286: Be able to bulk add children products of product models to group

## Improve Julia's experience

- PIM-7294: As Julia, I don't want to remove a family with products

## Technical improvements

- Add typescript support

## BC Breaks

- Remove methods `getAssociations`, `setAssociations`, `addAssociation`, `removeAssociation`, `getAssociationForType` and `getAssociationForTypeCode` from `Pim\Component\Catalog\Model\ProductInterface`. These methods are now in the `Pim\Component\Catalog\Model\EntityWithAssociationsInterface`.
- Change signature of `Pim\Component\Catalog\Builder\ProductBuilderInterface::addMissingAssociations` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface::findMissingAssociationTypes` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Model\AssociationInterface::setOwner` which now accepts a `Pim\Component\Catalog\Model\EntityWithAssociationsInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel` constructor to add the `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver`
- Change signature of `Pim\Component\Component\Catalog\ProductBuilder` constructor to add the `Pim\Component\Catalog\Association\MissingAssociationAdder`
- `Pim\Component\Catalog\Model\ProductModelInterface` now implements `Pim\Component\Catalog\Model\EntityWithAssociationsInterface`

## New jobs

**IMPORTANT**: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.

- Add the job instance `add_to_group`: `bin/console akeneo:batch:create-job "Akeneo Mass Edit Connector" "add_to_group" "mass_delete" "add_to_group" '{}' "Mass add product to group" --env=prod`

## Migrations

Please run the doctrine migrations command in order to see the new catalog volume monitoring screen: `bin/console doctrine:migrations:migrate --env=prod`
