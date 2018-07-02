/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/field',
    'pim/fetcher-registry',
    'pim/template/form/common/fields/select',
    'pim/user-context'
],
function (
    $,
    _,
    BaseField,
    fetcherRegistry,
    template,
    UserContext
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
        scopes: [],
        multiple: false,

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
                fetcherRegistry.getFetcher('channel').fetchAll()
                    .then(function (scopes) {
                        this.scopes = scopes;
                    }.bind(this))
            );
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(Object.assign(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.scopes),
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
         * @param {Array} scopes
         */
        formatChoices: function (scopes) {
            return scopes.reduce((result, channel) => {
                const label = channel.labels[UserContext.get('user_default_locale')];
                result[channel.code] = label !== undefined ? label : '[' + channel.code + ']';
                return result;
            }, {});
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
