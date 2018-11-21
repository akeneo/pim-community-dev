/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/field',
    'oro/translator',
    'pim/template/form/common/fields/select'
],
function (
    $,
    _,
    BaseField,
    __,
    template
) {
    return BaseField.extend({
        events: {
            'change select': function (event) {
                this.errors = [];
                this.updateModel(this.getFieldValue(event.target));
                this.getRoot().render();
            }
        },
        template: _.template(template),

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            if (undefined === this.getModelValue() && _.has(this.config, 'defaultValue')) {
                this.updateModel(this.config.defaultValue);
            }

            return this.template(_.extend(templateContext, {
                value: this.getModelValue(),
                choices: this.formatChoices(this.config.choices || []),
                multiple: this.config.isMultiple,
                labels: {
                    defaultLabel: this.getDefaultLabel()
                }
            }));
        },

        /**
         * Returns the default label for empty value
         *
         * @returns {string}
         */
        getDefaultLabel: function () {
            return '';
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('select.select2').select2({
                allowClear: this.config.allowClear !== undefined ? this.config.allowClear : true
            });
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
            const value = $(field).val();

            return this.config.isMultiple && null === value ? [] : value;
        }
    });
});
