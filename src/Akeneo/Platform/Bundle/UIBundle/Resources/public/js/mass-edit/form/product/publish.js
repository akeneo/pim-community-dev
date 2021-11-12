'use strict';
/**
 * Change status operation
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'underscore',
  'oro/translator',
  'pim/mass-edit-form/product/operation',
  'pimee/template/mass-edit/product/publish',
], function(_, __, BaseOperation, template) {
  return BaseOperation.extend({
    template: _.template(template),

    /**
     * {@inheritdoc}
     */
    render: function() {
      this.$el.html(
        this.template({
          warning: __(this.config.warning, {itemsCount: this.getFormData().itemsCount}),
        })
      );

      return this;
    },
  });
});
