'use strict';

define(['pim/product-edit-form/associated-product-row', 'pim/security-context'], function(
  AssociatedProductRow,
  SecurityContext
) {
  return AssociatedProductRow.extend({
    /**
     * {@inheritdoc}
     */
    canRemoveAssociation: function() {
      const isProductSourceOwner = this.model.collection.meta?.source?.meta?.is_owner;
      const permissionGranted = SecurityContext.isGranted('pim_enrich_associations_remove');
      const fromInheritance = this.model.get('from_inheritance');

      return isProductSourceOwner && permissionGranted && !fromInheritance;
    },
  });
});

