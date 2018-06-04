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
    'pim/fetcher-registry',
    'oro/translator',
    'pim/template/form/common/fields/select'
],
function (
    $,
    _,
    BaseField,
    fetcherRegistry,
    __,
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
        refData: [],

        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                BaseField.prototype.configure.apply(this, arguments),
                fetcherRegistry.getFetcher('reference-data-configuration').fetchAll()
                    .then(function (refData) {
                        this.refData = _.pick(refData, function (refDataItem) {
                            return this.config.refDataType === refDataItem.type;
                        }.bind(this));
                    }.bind(this))
            );
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            if (!_.has(this.getFormData(), this.fieldName) && _.has(this.config, 'defaultValue')) {
                this.updateModel(this.config.defaultValue);
            }

            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.refData),
                multiple: false,
                labels: {
                    defaultLabel: __('pim_enrich.entity.attribute.property.reference_data_name.choose')
                }
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('select.select2').select2({allowClear: true});
        },

        /**
         * @param {Object} refData
         */
        formatChoices: function (refData) {
            return _.mapObject(refData, function (refDataItem) {
                return refDataItem.name;
            });
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        }
    });
});
