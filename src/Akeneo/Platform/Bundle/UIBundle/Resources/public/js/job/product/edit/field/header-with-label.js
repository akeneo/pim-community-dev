'use strict';

/**
 * Switch view extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  'underscore',
  'pim/job/common/edit/field/field',
  'pim/template/export/common/edit/field/switch',
  'pim/common/property',
  'bootstrap.bootstrapswitch',
], function (_, BaseField, fieldTemplate, propertyAccessor) {
  return BaseField.extend({
    fieldTemplate: _.template(fieldTemplate),
    events: {
      'change input': 'updateState',
    },

    /**
     * {@inherit}
     */
    configure: function () {
      this.listenTo(this.getRoot(), 'job.with_label.change', () => {
        if (this.getFormData().configuration.with_label) {
          const data = propertyAccessor.updateProperty(this.getFormData(), this.getFieldCode(), true);

          this.setData(data);
        }
        this.render();
      });

      return BaseField.prototype.configure.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    render: function () {
      if (!this.getFormData().configuration.with_label) {
        this.$el.html('');

        return this;
      }

      BaseField.prototype.render.apply(this, arguments);

      this.$('.switch').bootstrapSwitch();
    },

    /**
     * Get the field dom value
     *
     * @return {string}
     */
    getFieldValue: function () {
      return this.$('input[type="checkbox"]').prop('checked');
    },
  });
});
