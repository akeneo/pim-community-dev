# 2.3.x

## Improve Julia's experience

- PIM-6795: As Julia, I would like to display only the current level attributes

## Technical improvements

- Add typescript support

## BC Breaks

- Remove methods `getAssociations`, `setAssociations`, `addAssociation`, `removeAssociation`, `getAssociationForType` and `getAssociationForTypeCode` from `Pim\Component\Catalog\Model\ProductInterface`. These methods are now in the `Pim\Component\Catalog\Model\AssociationAwareInterface`.
- Rename `Pim\Component\Catalog\Model\AssociationInterface` to `Pim\Component\Catalog\Model\ProductAssociationInterface`
