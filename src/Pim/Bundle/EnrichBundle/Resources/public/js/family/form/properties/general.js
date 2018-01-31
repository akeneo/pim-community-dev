'use strict';

/**
 * Extension used for family properties tab general tab section
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/fetcher-registry',
    'pim/common/property',
    'pim/template/form/tab/section',
    'oro/loading-mask'
    ],
    function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        propertyAccessor,
        template,
        LoadingMask
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            errors: [],

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
                this.$el.html(this.template({
                    __: __,
                    sectionTitle: this.config.label,
                    dropZone: this.config.dropZone
                }));

                this.renderExtensions();
            },

            /**
             * Get the validation errors for the given field
             *
             * @param {string} field
             *
             * @return {mixed}
             */
            getValidationErrorsForField: function (field) {
                return propertyAccessor
                    .accessProperty(
                        this.errors,
                        field,
                        []
                    );
            },

            /**
             * Sets errors
             *
             * @param {Object} errors
             */
            setValidationErrors: function (errors) {
                this.errors = errors.response;
            },

            /**
             * Resets validation errors
             */
            resetValidationErrors: function () {
                this.errors = {};
                this.render();
            },

            /**
             * Shows the loading mask
             */
            showLoadingMask: function () {
                this.loadingMask = new LoadingMask();
                this.loadingMask.render().$el.appendTo(this.getRoot().$el).show();
            },

            /**
             * Hides the loading mask
             */
            hideLoadingMask: function () {
                this.loadingMask.hide().$el.remove();
            }
        });
    }
);

