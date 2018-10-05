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
    'pim/template/channel/tab/properties/general/locales',
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
            initialLocales: null,
            locales: null,

            /**
             * Configures this extension.
             *
             * @return {Promise}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.render.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_save', this.setCurrentLocales.bind(this));

                this.initialLocales = this.getFormData().locales;

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
                    this.locales = locales;
                    this.$el.html(this.template({
                        currentLocales: this.getFormData().locales,
                        locales: locales,
                        label: __('pim_enrich.entity.locale.plural_label'),
                        requiredLabel: __('pim_common.required_label'),
                        errors: this.getParent().getValidationErrorsForField('locales')
                    }));

                    this.$('.select2').select2().on('change', this.updateState.bind(this));

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
                var localesToSet = [];

                _.each(event.val, function (code) {
                    localesToSet.push(
                        _.find(this.locales, function (locale) {
                            return locale.code === code;
                        })
                    );
                }.bind(this));

                this.setLocales(localesToSet);
            },

            /**
             * Sets specified locales into root model.
             *
             * @param {Array} locales
             */
            setLocales: function (locales) {
                var data = this.getFormData();
                data.locales = locales;
                this.setData(data);
            },

            /**
             * Sets current locales
             */
            setCurrentLocales: function () {
                var oldLocales = this.initialLocales;
                var newLocales = this.getFormData().locales;

                if (!_.isEqual(oldLocales, newLocales)) {
                    this.getRoot().trigger('pim_enrich:form:entity:locales_updated');

                    this.initialLocales = newLocales;
                }

            }
        });
    }
);
