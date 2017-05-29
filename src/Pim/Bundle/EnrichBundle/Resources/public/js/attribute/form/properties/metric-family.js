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
        fieldName: 'metric_family',
        events: {
            'change select': function (event) {
                this.updateModel(event.target);
                this.getRoot().render();
            }
        },

        render: function () {
            fetcherRegistry.getFetcher('measure').fetchAll()
                .then(function (measures) {
                    this.$el.html(this.template({
                        value: this.getFormData()[this.fieldName],
                        fieldName: this.fieldName,
                        choices: this.formatChoices(measures),
                        labels: {
                            field: __('pim_enrich.form.attribute.tab.properties.' + this.fieldName),
                            required: __('pim_enrich.form.required')
                        },
                        multiple: false
                    }));

                    this.$('select.select2').select2();

                    this.renderExtensions();
                    this.delegateEvents();
                }.bind(this));
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
         * Transforms:
         *
         * {
         *     Area: {...},
         *     Binary: {...}
         * }
         *
         * into:
         *
         * {
         *     Area: "Surface",
         *     Binary: "Binaire"
         * }
         *
         * (for locale fr_FR)
         *
         * @param {Object} measures
         */
        formatChoices: function (measures) {
            var metricFamilyCodes = _.keys(measures);

            return _.object(
                metricFamilyCodes,
                _.map(metricFamilyCodes, __)
            );
        }
    });
});
