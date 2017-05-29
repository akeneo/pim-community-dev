/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/fetcher-registry',
    'text!pim/template/attribute/tab/properties/select'
],
function (
    _,
    __,
    BaseForm,
    fetcherRegistry,
    template
) {
    return BaseForm.extend({
        className: 'AknFieldContainer',
        template: _.template(template),
        fieldName: 'available_locales',
        events: {
            'change select': function (event) {
                this.updateModel(event.target);
                this.getRoot().render();
            }
        },
        availableLocales: [],

        configure: function () {
            return $.when(
                BaseForm.prototype.configure.apply(this, arguments),
                fetcherRegistry.getFetcher('locale').fetchActivated()
                    .then(function (availableLocales) {
                        this.availableLocales = availableLocales;
                    }.bind(this))
            );
        },

        render: function () {
            if (!this.getFormData().is_locale_specific) {
                this.$el.empty();
                return;
            }

            this.$el.html(this.template({
                value: this.getFormData()[this.fieldName],
                fieldName: this.fieldName,
                choices: this.formatChoices(this.availableLocales),
                labels: {
                    field: __('pim_enrich.form.attribute.tab.properties.' + this.fieldName)
                },
                multiple: true
            }));

            this.$('select.select2').select2();

            this.renderExtensions();
            this.delegateEvents();
        },

        /**
         * @param {Object} field
         */
        updateModel: function (field) {
            var newData = {};
            newData[this.fieldName] = $(field).val();

            this.setData(newData);
        },

        /**
         * @param {Array} locales
         */
        formatChoices: function (locales) {
            return _.object(
                _.pluck(locales, 'code'),
                _.pluck(locales, 'label')
            );
        }
    });
});
