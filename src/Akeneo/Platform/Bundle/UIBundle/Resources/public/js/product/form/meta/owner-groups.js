'use strict';

define([
  'underscore',
  'oro/translator',
  'pim/form',
  'oro/mediator',
  'pimee/template/product/meta/owner-groups',
], function(_, __, BaseForm, mediator, formTemplate) {
  return BaseForm.extend({
    className: 'AknColumn-block product-owner-groups',

    template: _.template(formTemplate),

    /**
     * {@inheritdoc}
     */
    render: function() {
      this.$el.html(
        this.template({
          label: __('pimee_enrich.entity.product.module.meta.owner_groups'),
          groups: this.getOwnerGroups(this.getFormData()),
        })
      );

      return this;
    },

    /**
     * Get human readable owner groups for the given product
     *
     * @param {Object} product
     *
     * @returns {string}
     */
    getOwnerGroups: function(product) {
      return _.pluck(product.meta.owner_groups, 'name').join(', ');
    },
  });
});
