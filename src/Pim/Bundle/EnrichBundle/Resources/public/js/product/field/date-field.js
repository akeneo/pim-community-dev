'use strict';
/**
 * Date field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/field',
        'underscore',
        'text!pim/template/product/field/date',
        'pim/date-context',
        'bootstrap.datetimepicker'
    ],
    function (
        Field,
        _,
        fieldTemplate,
        DateContext
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                language: DateContext.get('language'),
                pickTime: false
            },
            events: {
                'change .field-input:first input[type="text"]': 'updateModel',
                'click .field-input:first input[type="text"]': 'click'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            click: function () {
                this.$('.datetimepicker').datetimepicker(this.datetimepickerOptions).datetimepicker('show');

                this.$('.datetimepicker').on('changeDate', function (e) {
                    this.setCurrentValue(this.$(e.target).find('input[type="text"]').val());
                }.bind(this));
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            }
        });
    }
);
