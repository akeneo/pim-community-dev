# 2.3.x

## Improve Julia's experience

- PIM-6897: As Julia, I would like to update the family variant labels from the UI

# 2.3.0-ALPHA1 (2018-04-27)

## Improve Julia's experience

- PIM-6795: As Julia, I would like to display only the current level attributes
- PIM-7284: Be able to bulk change the status of children products if product models are selected
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.
- PIM-6989: As Julia, I would like to associate product models by import
- PIM-7286: Be able to bulk add children products of product models to group

## Technical improvements

- Add typescript support

## BC Breaks

- Remove methods `getAssociations`, `setAssociations`, `addAssociation`, `removeAssociation`, `getAssociationForType` and `getAssociationForTypeCode` from `Pim\Component\Catalog\Model\ProductInterface`. These methods are now in the `Pim\Component\Catalog\Model\AssociationAwareInterface`.
- Change signature of `Pim\Component\Catalog\Builder\ProductBuilderInterface::addMissingAssociations` which now accepts a `Pim\Component\Catalog\Model\AssociationAwareInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface::findMissingAssociationTypes` which now accepts a `Pim\Component\Catalog\Model\AssociationAwareInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Model\AssociationInterface::setOwner` which now accepts a `Pim\Component\Catalog\Model\AssociationAwareInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel` constructor to add the `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver`
- Change signature of `Pim\Component\Component\Catalog\ProductBuilder` constructor to add the `Pim\Component\Catalog\Association\MissingAssociationAdder`
- `Pim\Component\Catalog\Model\ProductModelInterface` now implements `Pim\Component\Catalog\Model\AssociationAwareInterface`

## New jobs
IMPORTANT: In order for your PIM to work properly, you will need to run the following commands to add the missing job instances.
- Add the job instance `add_to_group`: `bin/console akeneo:batch:create-job "Akeneo Mass Edit Connector" "add_to_group" "mass_delete" "add_to_group" '{}' "Mass add product to group" --env=prod`

## Migrations

Please run the doctrine migrations command in order to see the new catalog volume monitoring screen: `bin/console doctrine:migrations:migrate --env=prod`
