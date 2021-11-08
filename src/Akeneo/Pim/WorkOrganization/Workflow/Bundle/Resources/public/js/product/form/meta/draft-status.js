'use strict';

define(['underscore', 'oro/translator', 'pim/form', 'pimee/template/product/meta/draft-status'], function(
  _,
  __,
  BaseForm,
  formTemplate
) {
  return BaseForm.extend({
    className: 'AknColumn-block draft-status',

    template: _.template(formTemplate),

    /**
     * {@inheritdoc}
     */
    configure: function() {
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function() {
      var product = this.getFormData();
      var html = '';

      if (!product.meta.is_owner) {
        html = this.template({
          label: __('pimee_enrich.entity.product.module.meta.draft_status'),
          draftStatus: this.getDraftStatus(product),
        });
      }

      this.$el.html(html);

      return this;
    },

    /**
     * Get the human readable draft status
     *
     * @param {Object} product
     *
     * @returns {string}
     */
    getDraftStatus: function(product) {
      var status;

      switch (product.meta.draft_status) {
        case 0:
          status = __('pimee_enrich.entity.product.module.meta.draft.in_progress');
          break;
        case 1:
          status = __('pimee_enrich.entity.product.module.meta.draft.sent_for_approval');
          break;
        default:
          status = __('pimee_enrich.entity.product.module.meta.draft.working_copy');
          break;
      }

      return status;
    },
  });
});
