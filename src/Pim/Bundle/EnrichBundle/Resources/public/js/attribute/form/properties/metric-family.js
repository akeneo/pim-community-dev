/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form/common/fields/field',
    'pim/fetcher-registry',
    'pim/template/form/common/fields/select'
],
function (
    $,
    _,
    __,
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
                    defaultLabel: __('pim_enrich.entity.attribute.property.metric_family.choose')
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
            const metricFamilyCodes = Object.keys(measures);

            return _.object(
                metricFamilyCodes,
                metricFamilyCodes.map((metricFamilyCode) => {
                    return __(`pim_measure.families.${metricFamilyCode}`);
                })
            );
        },

        /**
         * {@inheritdoc}
         *
         * Override to reset the default metric unit each time the metric family changes.
         */
        updateModel: function () {
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
