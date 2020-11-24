'use strict';

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define([
  'jquery',
  'underscore',
  'pim/form/common/fields/field',
  'datepicker',
  'pim/formatter/date',
  'pim/template/form/common/fields/date',
  'pimui/js/date-context',
], function ($, _, BaseField, Datepicker, DateFormatter, template, {dateContext}) {
  return BaseField.extend({
    events: {
      'change input': function (event) {
        this.errors = [];
        this.updateModel(this.getFieldValue(event.target));
        this.getRoot().render();
      },
    },
    template: _.template(template),
    modelDateFormat: 'yyyy-MM-dd',

    /**
     * {@inheritdoc}
     */
    renderInput: function (templateContext) {
      var value = DateFormatter.format(this.getModelValue(), this.modelDateFormat, dateContext.get('date').format);

      return this.template(
        _.extend(templateContext, {
          value,
          readOnly: this.readOnly,
        })
      );
    },

    /**
     * {@inheritdoc}
     */
    postRender: function () {
      Datepicker.init(this.$('.date-wrapper'), {
        format: dateContext.get('date').format,
        defaultFormat: dateContext.get('date').defaultFormat,
        language: dateContext.get('language'),
      }).on(
        'changeDate',
        function () {
          this.errors = [];
          this.updateModel(this.getFieldValue(this.$('input')[0]));
          this.$('.date-wrapper').datetimepicker('destroy');
          this.getRoot().render();
        }.bind(this)
      );
    },

    /**
     * {@inheritdoc}
     */
    getFieldValue: function (field) {
      var dateFormat = dateContext.get('date').format;
      var value = $(field).val();

      return DateFormatter.format(value, dateFormat, this.modelDateFormat);
    },
  });
});
