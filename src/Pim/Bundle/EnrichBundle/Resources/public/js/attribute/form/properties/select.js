/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/attribute-edit-form/properties/field',
    'oro/translator',
    'text!pim/template/attribute/tab/properties/select'
],
function (
    _,
    BaseField,
    __,
    template
) {
    return BaseField.extend({
        template: _.template(template),

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.config.choices || []),
                multiple: this.config.isMultiple,
                labels: {
                    defaultLabel: ''
                }
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('select.select2').select2({allowClear: true});
        },

        /**
         * @param {Array} choices
         */
        formatChoices: function (choices) {
            return Array.isArray(choices) ?
                _.object(choices, choices) :
                _.mapObject(choices, __)
            ;
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        }
    });
});
