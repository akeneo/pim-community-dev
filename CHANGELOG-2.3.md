# 2.3.x

## Improve Julia's experience

- PIM-6795: As Julia, I would like to display only the current level attributes.
- PIM-7284: Be able to bulk change the status of children products if product models are selected.
- PIM-7296: As Julia, I would like to change the parent of a variant product by import.

## Technical improvements

- Add typescript support

## BC Breaks

- Remove methods `getAssociations`, `setAssociations`, `addAssociation`, `removeAssociation`, `getAssociationForType` and `getAssociationForTypeCode` from `Pim\Component\Catalog\Model\ProductInterface`. These methods are now in the `Pim\Component\Catalog\Model\AssociationAwareInterface`.
- Change signature of `Pim\Component\Catalog\Builder\ProductBuilderInterface::addMissingAssociations` which now accepts a `Pim\Component\Catalog\Model\AssociationAwareInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface::findMissingAssociationTypes` which now accepts a `Pim\Component\Catalog\Model\AssociationAwareInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
- Change signature of `Pim\Component\Catalog\Model\AssociationInterface::setOwner` which now accepts a `Pim\Component\Catalog\Model\AssociationAwareInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`
