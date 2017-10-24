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
    'pim/template/form/common/fields/select'
],
function (
    $,
    _,
    BaseField,
    __,
    template
) {
    return BaseField.extend({
        events: {
            'keyup input': (event) => {
                this.errors = [];
                this.updateModel(this.getAmountValue(event.target));
            },
            'change select': (event) => {
                this.errors = [];
                this.updateModel(this.getUnitValue(event.target));
                this.getRoot().render();
            }
        },
        template: _.template(template),
        metricFamily: null,

        initialize() {
            this.metricFamily = null;

            return BaseField.prototype.initialize.apply(this, arguments);
        },

        setMetricFamily(metricFamily) {
            this.metricFamily = metricFamily;
        },

        /**
         * {@inheritdoc}
         */
        renderInput (templateContext) {
            fetcherRegistry.getFetcher('measure').fetchAll()
                .then((measures) => {
                    return this.template(_.extend(templateContext, {
                        value: this.getFormData()[this.fieldName],
                        unitChoices: this.formatChoices(measures[this.metricFamily].units)
                    }));
                });
        },

        /**
         * {@inheritdoc}
         */
        postRender () {
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
        formatChoices (units) {
            const unitCodes = _.keys(units);

            return _.object(
                unitCodes,
                _.map(unitCodes, __)
            );
        },

        /**
         * {@inheritdoc}
         */
        getAmountValue (field) {
            return $(field).val();
        },

        /**
         * {@inheritdoc}
         */
        getUnitValue (field) {
            return $(field).val();
        }
    });
});
