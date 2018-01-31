'use strict';
/**
 * Properties form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/template/export/common/edit/properties',
        'pim/form',
        'pim/common/property'
    ],
    function (
        _,
        __,
        template,
        BaseForm,
        propertyAccessor
    ) {
        return BaseForm.extend({
            template: _.template(template),
            errors: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.config.tabCode ? this.config.tabCode : this.code,
                    label: __(this.config.tabTitle)
                });
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_fetch',
                    this.resetValidationErrors.bind(this)
                );
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render.bind(this));
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:validation_error',
                    this.setValidationErrors.bind(this)
                );
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Set validation errors after save request failure
             *
             * @param {event} event
             */
            setValidationErrors: function (event) {
                this.errors = event.response;
            },

            /**
             * Remove validation error
             */
            resetValidationErrors: function () {
                this.errors = {};
            },

            /**
             * Get the validtion errors for the given field
             *
             * @param {string} field
             *
             * @return {mixed}
             */
            getValidationErrorsForField: function (field) {
                return propertyAccessor.accessProperty(this.errors, field, null);
            },

            /**
             * {@inherit}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({__: __})
                );

                this.renderExtensions();
            }
        });
    }
);
