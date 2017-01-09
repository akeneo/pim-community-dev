'use strict';

/**
 * Module used to display the conversion unit properties of a channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/channel/tab/properties/conversion-unit',
        'pim/user-context',
        'jquery.select2'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template,
        UserContext
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            catalogLocale: UserContext.get('catalogLocale'),
            config: null,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                FetcherRegistry.getFetcher('conversion-unit').fetchAll().then(function (attributes) {

                    this.$el.html(this.template({
                        conversionUnits: this.getFormData().conversionUnits,
                        attributes: attributes,
                        catalogLocale: this.catalogLocale,
                        label: __(this.config.label),
                        fieldBaseId: this.config.fieldBaseId,
                        doNotConvertLabel: __('pim_enrich.form.channel.tab.properties.conversion_unit.do_not_convert')
                    }));

                    this.$('.select2').select2().on('change', this.updateState.bind(this));
                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Sets new attribute conversion unit on change.
             *
             * @param {Object} event
             */
            updateState: function(event) {
                this.setAttributeConversionUnit(
                    $(event.target).data('attribute'),
                    $(event.target).val()
                );

            },

            /**
             * Sets specified conversion unit settings into form model.
             *
             * @param {String} attribute
             * @param {String} value
             */
            setAttributeConversionUnit: function (attribute, value) {
                var data = this.getFormData();

                var key = _.findIndex(data.conversion_units, function (unit) { return _.has(unit, attribute); });
                if (0 > key) {
                    var conversionUnit = {};
                    conversionUnit[attribute] = value;
                    data.conversion_units.push(conversionUnit);
                } else {
                    data.conversion_units[key][attribute] = value;
                }

                this.setData(data);
            }
        });
    }
);
