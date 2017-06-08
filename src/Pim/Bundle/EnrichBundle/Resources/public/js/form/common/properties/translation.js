'use strict';

/**
 * Module used to display the localized properties of an object
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'pim/form',
        'pim/fetcher-registry',
        'pim/template/form/properties/translation'
    ],
    function (
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection translation-container',
            template: _.template(template),
            events: {
                'change .label-field': 'updateModel'
            },
            validationErrors: {},
            locales: null,

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

                return BaseForm.prototype.configure.apply(this, arguments);
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

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.locales) {
                    FetcherRegistry.getFetcher('locale')
                        .search({'activated': true, 'cached': true})
                        .then(function (locales) {
                            this.locales = locales;
                            this.render();
                        }.bind(this));
                }

                this.$el.html(this.template({
                    model: this.getFormData(),
                    locales: this.locales,
                    errors: this.validationErrors,
                    label: this.config.label,
                    fieldBaseId: this.config.fieldBaseId,
                    isReadOnly: false /* false as default default value */
                }));

                this.delegateEvents();

                this.renderExtensions();
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
                FetcherRegistry.getFetcher('locale')
                    .search({'activated': true, 'cached': false})
                    .then(function (locales) {
                        if (!_.isEqual(this.locales, locales)) {
                            this.locales = locales;

                            return this.render();
                        }

                    }.bind(this));
            }
        });
    }
);
