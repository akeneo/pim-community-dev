/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/attribute-edit-form/properties/field',
    'pim/fetcher-registry',
    'text!pim/template/attribute/tab/properties/select'
],
function (
    _,
    __,
    BaseField,
    fetcherRegistry,
    template
) {
    return BaseField.extend({
        template: _.template(template),
        measures: {},

        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                BaseField.prototype.configure.apply(this, arguments),
                fetcherRegistry.getFetcher('measure').fetchAll()
                    .then(function (measures) {
                        this.measures = measures;
                    }.bind(this))
            );
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.measures),
                multiple: false,
                labels: {
                    defaultLabel: __('pim_enrich.entity.attribute.metric_family.default_value')
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
        },

        /**
         * {@inheritdoc}
         *
         * Override to reset the default metric unit each time the metric family changes.
         */
        updateModel: function (field) {
            BaseField.prototype.updateModel.apply(this, arguments);

            this.setData({default_metric_unit: null}, {silent: true});
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        }
    });
});
