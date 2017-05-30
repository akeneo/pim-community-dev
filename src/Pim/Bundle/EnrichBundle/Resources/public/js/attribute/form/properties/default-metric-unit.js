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
            var metricFamily = this.getFormData().metric_family;

            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.measures[metricFamily].units),
                multiple: false,
                labels: {
                    defaultLabel: __('pim_enrich.entity.attribute.default_metric_unit.default_value')
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
         * This field shouldn't be displayed if the attribute has no metric family defined yet.
         */
        isVisible: function () {
            return undefined !== this.getFormData().metric_family && null !== this.getFormData().metric_family;
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
        formatChoices: function (units) {
            var unitCodes = _.keys(units);

            return _.object(
                unitCodes,
                _.map(unitCodes, __)
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
