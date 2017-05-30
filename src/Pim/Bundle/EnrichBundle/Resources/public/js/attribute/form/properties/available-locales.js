/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/attribute-edit-form/properties/field',
    'pim/fetcher-registry',
    'text!pim/template/attribute/tab/properties/select'
],
function (
    _,
    BaseField,
    fetcherRegistry,
    template
) {
    return BaseField.extend({
        template: _.template(template),
        availableLocales: [],

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
                multiple: true,
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
         * {@inheritdoc}
         *
         * This field shouldn't be displayed if the attribute is not locale specific.
         */
        isVisible: function () {
            return undefined !== this.getFormData().is_locale_specific && this.getFormData().is_locale_specific;
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
            return $(field).val();
        }
    });
});
