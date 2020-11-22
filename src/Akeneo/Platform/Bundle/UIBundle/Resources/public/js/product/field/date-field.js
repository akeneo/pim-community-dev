'use strict'

import {dateContext} from 'pimui/js/date-context';


/**
 * Date field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['jquery', 'pim/field', 'underscore', 'pim/template/product/field/date', 'datepicker'], function (
  $,
  Field,
  _,
  fieldTemplate,
  Datepicker
) {
  return Field.extend({
    fieldTemplate: _.template(fieldTemplate),
    events: {
      'change .field-input:first input[type="text"]': 'updateModel',
      'click .field-input:first input[type="text"]': 'click',
    },
    datetimepickerOptions: {
      format: dateContext.get('date').format,
      defaultFormat: dateContext.get('date').defaultFormat,
      language: dateContext.get('language'),
    },
    renderInput: function (context) {
      return this.fieldTemplate(context);
    },
    click: function (event) {
      var clickedElement = $(event.currentTarget).parent();
      var picker = this.$('.datetimepicker');

      Datepicker.init(picker, this.datetimepickerOptions);
      clickedElement.datetimepicker('show');

      picker.on(
        'changeDate',
        function (e) {
          this.setCurrentValue(this.$(e.target).find('input[type="text"]').val());
        }.bind(this)
      );
    },
    updateModel: function () {
      var data = this.$('.field-input:first input[type="text"]').val();
      data = '' === data ? this.attribute.empty_value : data;

      this.setCurrentValue(data);
    },
  });
});
