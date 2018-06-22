# 2.3.0-BETA1 (2018-06-21)

## Monitore your catalog volume

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
