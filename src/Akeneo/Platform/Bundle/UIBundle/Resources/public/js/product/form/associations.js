'use strict';
/**
 * Associations tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(['pim/product-edit-form/associations', 'pim/security-context'], function(Associations, SecurityContext) {
  return Associations.extend({
    /**
     * {@inheritdoc}
     */
    isVisible: function() {
      return true;
    },

    /**
     * {@inheritdoc}
     */
    isAddAssociationsVisible: function() {
      const isProductOwner = this.getFormData().meta.is_owner;
      const isAddAssociationsAclGranted = SecurityContext.isGranted(this.config.aclAddAssociations);

      return isProductOwner && isAddAssociationsAclGranted;
    },
  });
});
