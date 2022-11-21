'use strict';
/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['underscore', 'oro/translator', 'pim/form', 'pim/template/form/meta/uuid'], function (
  _,
  __,
  BaseForm,
  formTemplate
) {
  return BaseForm.extend({
    tagName: 'span',
    className: 'AknTitleContainer-metaItem',
    template: _.template(formTemplate),

    /**
     * {@inheritdoc}
     */
    initialize: function (meta) {
      this.config = meta.config;
      this.label = __(this.config.label);

      BaseForm.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      var product = this.getFormData();
      var html = this.template({
        label: this.label,
        uuid: _.result(product.meta, 'uuid', null),
      });

      this.$el.html(html);

      return this;
    },
  });
});
