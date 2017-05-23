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
        fieldName: 'default_metric_unit',
        events: {
            'change select': function (event) {
                this.updateModel(event.target);
                this.getRoot().render();
            }
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), this.getRoot().preUpdateEventName, function (newData) {
                var oldData = this.getFormData();

                if (_.has(newData, 'metric_family') && oldData.metric_family !== newData.metric_family) {
                    var unitNewData = {};
                    unitNewData[this.fieldName] = null;

                    this.setData(unitNewData, {silent: true});
                }
            }.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        render: function () {
            fetcherRegistry.getFetcher('measure').fetchAll()
                .then(function (measures) {
                    var metricFamily = this.getFormData().metric_family;
                    var choices = metricFamily ? this.formatChoices(measures[metricFamily].units) : [];

                    this.$el.html(this.template({
                        value: this.getFormData()[this.fieldName],
                        fieldName: this.fieldName,
                        choices: choices,
                        labels: {
                            field: __('pim_enrich.form.attribute.tab.properties.' + this.fieldName),
                            required: __('pim_enrich.form.required'),
                            defaultLabel: __('pim_enrich.entity.attribute.default_metric_unit.default_value')
                        }
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
        }
    });
});
