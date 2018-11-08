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
    'pim/fetcher-registry',
    'pim/template/form/common/fields/metric'
], function (
    $,
    _,
    BaseField,
    __,
    FetcherRegistry,
    template
) {
    return BaseField.extend({
        events: {
            'keyup input': function () {
                this.errors = [];
                this.updateModel(this.getValue());
            },
            'change select': function () {
                this.errors = [];
                this.updateModel(this.getValue());
                this.getRoot().render();
            }
        },
        template: _.template(template),
        metricFamily: null,
        defaultMetricUnit: null,

        /**
         * {@inheritdoc}
         */
        initialize() {
            this.metricFamily = null;
            this.defaultMetricUnit = null;

            return BaseField.prototype.initialize.apply(this, arguments);
        },

        /**
         * @param {String} metricFamily
         */
        setMetricFamily(metricFamily) {
            this.metricFamily = metricFamily;
        },

        /**
         * @param {String} defaultMetricUnit
         */
        setDefaultMetricUnit(defaultMetricUnit) {
            this.defaultMetricUnit = defaultMetricUnit;
        },

        /**
         * {@inheritdoc}
         */
        renderInput(templateContext) {
            return this.template(_.extend(templateContext, {
                value: {
                    amount: this.getModelValuePart('amount'),
                    unit: this.getModelValuePart('unit')
                }
            }));
        },

        /**
         * @param {String} name
         *
         * @returns {*|null}
         */
        getModelValuePart(name) {
            return undefined !== this.getModelValue()
                ? this.getModelValue()[name]
                : null;
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseField.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('measure').fetchAll()
            ).then((parentContext, measures) => {
                return Object.assign(
                    {},
                    parentContext,
                    {
                        unitChoices: this.formatChoices(measures[this.metricFamily].units),
                        defaultUnit: this.defaultMetricUnit
                    }
                );
            });
        },

        /**
         * {@inheritdoc}
         */
        postRender() {
            this.$('select.select2').select2({allowClear: true});
        },

        /**
         * Transforms:
         *
         * {
         *     BIT: {...},
         *     BYTE: {...}
         * }
         *
         * into:
         *
         * {
         *     BIT: "Bit",
         *     BYTE: "Octet"
         * }
         *
         * (for locale fr_FR)
         *
         * @param {Object} units
         */
        formatChoices(units) {
            const unitCodes = Object.keys(units);

            return _.object(
                unitCodes,
                unitCodes.map(unitCode => __(unitCode))
            );
        },

        /**
         * @return {Object}
         */
        getValue() {
            return {
                amount: this.$('.amount').val(),
                unit: this.$('.unit').val()
            };
        }
    });
});
