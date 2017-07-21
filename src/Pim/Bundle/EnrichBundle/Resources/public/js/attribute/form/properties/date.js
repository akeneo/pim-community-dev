/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'pim/attribute-edit-form/properties/field',
    'datepicker',
    'pim/formatter/date',
    'pim/date-context',
    'pim/template/attribute/tab/properties/date'
],
function (
    $,
    _,
    BaseField,
    Datepicker,
    DateFormatter,
    DateContext,
    template
) {
    return BaseField.extend({
        template: _.template(template),
        modelDateFormat: 'yyyy-MM-dd',

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            var value = DateFormatter.format(
                this.getFormData()[this.fieldName],
                this.modelDateFormat,
                DateContext.get('date').format
            );

            return this.template(_.extend(templateContext, {
                value: value
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            Datepicker
                .init(
                    this.$('.date-wrapper'),
                    {
                        format: DateContext.get('date').format,
                        defaultFormat: DateContext.get('date').defaultFormat,
                        language: DateContext.get('language')
                    }
                )
                .on('changeDate', function () {
                    this.errors = [];
                    this.updateModel(this.getFieldValue(this.$('input')[0]));
                    this.$('.date-wrapper').datetimepicker('destroy');
                    this.getRoot().render();
                }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            var dateFormat = DateContext.get('date').format;
            var value = $(field).val();

            return DateFormatter.format(value, dateFormat, this.modelDateFormat);
        }
    });
});
