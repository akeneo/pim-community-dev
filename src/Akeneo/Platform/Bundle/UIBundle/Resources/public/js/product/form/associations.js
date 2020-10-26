'use strict';
/**
 * Associations tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(['underscore', 'pim/product-edit-form/associations'], function(_, Associations) {
  return Associations.extend({
    /**
     * {@inheritdoc}
     */
    isVisible: function() {
      return _.result(_.result(this.getFormData(), 'meta', {}), 'is_owner', false);
    },
  });
});
