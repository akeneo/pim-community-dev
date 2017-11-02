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

        initialize() {
            this.metricFamily = null;
            this.defaultMetricUnit = null;

            return BaseField.prototype.initialize.apply(this, arguments);
        },

        setMetricFamily(metricFamily) {
            this.metricFamily = metricFamily;
        },

        setDefaultMetricUnit(defaultMetricUnit) {
            this.defaultMetricUnit = defaultMetricUnit;
        },

        /**
         * {@inheritdoc}
         */
        renderInput(templateContext) {
            return this.template(_.extend(templateContext, {
                value: {
                    amount: undefined !== this.getModelValue()
                        ? this.getModelValue().amount
                        : null,
                    unit: undefined !== this.getModelValue()
                        ? this.getModelValue().unit
                        : null
                }
            }));
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
            const unitCodes = _.keys(units);

            return _.object(
                unitCodes,
                _.map(unitCodes, __)
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
