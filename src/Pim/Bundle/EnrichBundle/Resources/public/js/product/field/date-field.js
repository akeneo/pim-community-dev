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
        'bootstrap.bootstrapsdatepicker'
    ],
    function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            datepickerOptions: {
                todayHighlight: true
            },
            events: {
                'change .field-input:first input[type="text"]': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            postRender: function () {
                this.$('.datepicker-field').datepicker(this.datepickerOptions);
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            }
        });
    }
);
