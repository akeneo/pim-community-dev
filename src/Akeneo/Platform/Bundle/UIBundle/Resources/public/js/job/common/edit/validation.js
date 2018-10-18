'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/template/export/common/edit/validation',
    'oro/messenger',
    'pim/common/property'

], function ($, _, __, BaseForm, template, messenger, propertyAccessor) {
    return BaseForm.extend({
        template: _.template(template),
        errors: [],

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.setValidationErrors.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        setValidationErrors: function (event) {
            this.errors = event.response;

            this.getRoot().trigger('pim_enrich:form:entity:validation_error', event);
        },

        /**
         * Adds the extension to filters.
         * If there is an error for the current filter, we add an element to it.
         *
         * @param {Object} event
         */
        addFilterExtension: function (event) {
            var filter = event.filter;

            if (null !== propertyAccessor
                .accessProperty(this.errors, 'configuration.filters.data' + filter.getField())
            ) {
                var content = $(this.template({
                    errors: propertyAccessor.accessProperty(
                        this.errors,
                        'configuration.filters.data' + filter.getField()
                    )
                }));

                event.filter.addElement(
                    'below-input',
                    'validation',
                    content
                );
            }
        }
    });
});
