'use strict';

/**
 * Module used to display the localized properties of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'pim/form',
        'pim/fetcher-registry',
        'pim/template/form/properties/translation'
    ],
    function (
        $,
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'translation-container',
            template: _.template(template),
            events: {
                'change .label-field': 'updateModel'
            },
            validationErrors: {},
            locales: [],

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
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:pre_save',
                    this.onPreSave
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:bad_request',
                    this.onValidationError
                );

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:locales_updated',
                    this.onLocalesUpdated.bind(this)
                );

                return $.when(
                    this.getLocales()
                        .then(function (locales) {
                            this.locales = locales;
                        }.bind(this)),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },

            /**
             * Pre save callback
             */
            onPreSave: function () {
                this.validationErrors = {};

                this.render();
            },

            /**
             * On validation callback
             *
             * @param {Event} event
             */
            onValidationError: function (event) {
                this.validationErrors = event.response.translations ? event.response.translations : {};
                this.locales.forEach((locale) => {
                    const key = 'translations[' + locale.code + '].label';
                    if (event.response[key]) {
                        this.validationErrors[locale.code] = event.response[key];
                    }
                });

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    model: this.getFormData(),
                    locales: this.locales,
                    errors: this.validationErrors,
                    label: this.config.label,
                    fieldBaseId: this.config.fieldBaseId,
                    isReadOnly: this.isReadOnly()
                }));

                this.delegateEvents();

                this.renderExtensions();
            },

            /**
             * @returns {Boolean}
             */
            isReadOnly: function () {
                return false;
            },

            /**
             * @param {Object} event
             */
            updateModel: function (event) {
                var data = this.getFormData();

                if (Array.isArray(data.labels)) {
                    data.labels = {};
                }

                data.labels[event.target.dataset.locale] = event.target.value;

                this.setData(data);
            },

            /**
             * Updates locales if were updated
             */
            onLocalesUpdated: function () {
                this.getLocales()
                    .then(function (locales) {
                            this.locales = locales;

                            return this.render();
                    }.bind(this));
            },

            /**
             * Fetches and returns activated locales.
             *
             * @returns {Promise}
             */
            getLocales: function () {
                return FetcherRegistry.getFetcher('locale').fetchActivated({filter_locales: false});
            }
        });
    }
);
