'use strict';

/**
 * Module used to display the currencies general properties field of a channel
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
        'pim/template/channel/tab/properties/general/currencies',
        'jquery.select2'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'AknFieldContainer',
            template: _.template(template),

            /**
             * Configures this extension.
             *
             * @return {Promise}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                FetcherRegistry.getFetcher('currency').fetchAll().then(function (currencies) {
                    this.$el.html(this.template({
                        currentCurrencies: this.getFormData().currencies,
                        currencies: currencies,
                        errors: this.getParent().getValidationErrorsForField('currencies'),
                        label: __('pim_enrich.entity.currency.plural_label'),
                        requiredLabel: __('pim_common.required_label')
                    }));

                    this.$('.select2').select2().on('change', this.updateState.bind(this));

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Sets new currencies on change.
             *
             * @param {Object} event
             */
            updateState: function (event) {
                this.setCurrencies(event.val);
            },

            /**
             * Sets specified currencies into root model.
             *
             * @param {Array} codes
             */
            setCurrencies: function (codes) {
                if (null === codes) {
                    codes = [];
                }
                var data = this.getFormData();

                data.currencies = codes;
                this.setData(data);
            }
        });
    }
);
