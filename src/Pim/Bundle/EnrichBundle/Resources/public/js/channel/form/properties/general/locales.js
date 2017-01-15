'use strict';

/**
 * Module used to display the locales general properties field of a channel
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
        'text!pim/template/channel/tab/properties/general/locales',
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

                FetcherRegistry.getFetcher('locale').fetchAll().then(function (locales) {
                    this.$el.html(this.template({
                        currentLocales: this.getFormData().locales,
                        locales: locales,
                        label: __('pim_enrich.form.channel.tab.properties.locales'),
                        requiredLabel: __('pim_enrich.form.required'),
                        errors: this.getParent().getValidationErrorsForField('locales')
                    }));

                    this.$('.select2').select2().on('change', this.updateState.bind(this));

                    this.delegateEvents();
                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Sets new locales on change.
             *
             * @param {Object} event
             */
            updateState: function (event) {
                this.setLocales($(event.target).val());
            },

            /**
             * Sets specified locales into root model.
             *
             * @param {Array} codes
             */
            setLocales: function (codes) {
                if (null === codes) {
                    codes = [];
                }
                var data = this.getFormData();

                data.locales = codes;
                this.setData(data);
            }
        });
    }
);
