'use strict';

define(['underscore', 'oro/translator', 'pim/form', 'pimee/template/product/meta/published'], function(
  _,
  __,
  BaseForm,
  formTemplate
) {
  return BaseForm.extend({
    className: 'AknColumn-block published-version',

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

      if (product.meta.published) {
        this.$el.html(
          this.template({
            label: __('pimee_enrich.entity.product.module.meta.published_version'),
            publishedVersion: this.getPublishedVersion(product),
          })
        );
      } else {
        this.$el.html('');
      }

      return this;
    },

    /**
     * Get the published version number for the given product
     *
     * @param {Object} product
     *
     * @returns {int}
     */
    getPublishedVersion: function(product) {
      return _.result(product.meta.published, 'version', null);
    },
  });
});
