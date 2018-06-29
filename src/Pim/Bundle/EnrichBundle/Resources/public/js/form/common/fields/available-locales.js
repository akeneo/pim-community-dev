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
    'pim/fetcher-registry',
    'pim/template/form/common/fields/select'
],
function (
    $,
    _,
    BaseField,
    fetcherRegistry,
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
        availableLocales: [],
        multiple: true,

        /**
         * @param {Object} meta
         */
        initialize: function (meta) {
            BaseField.prototype.initialize.apply(this, arguments);

            if (undefined !== meta.config.multiple) {
                this.multiple = meta.config.multiple;
            }
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                BaseField.prototype.configure.apply(this, arguments),
                fetcherRegistry.getFetcher('locale').fetchActivated()
                    .then(function (availableLocales) {
                        this.availableLocales = availableLocales;
                    }.bind(this))
            );
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.availableLocales),
                multiple: this.multiple,
                labels: {
                    defaultLabel: ''
                }
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('select.select2').select2();
        },

        /**
         * @param {Array} locales
         */
        formatChoices: function (locales) {
            return _.object(
                _.pluck(locales, 'code'),
                _.pluck(locales, 'label')
            );
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            const value = $(field).val();

            return null === value ? [] : value;
        }
    });
});
