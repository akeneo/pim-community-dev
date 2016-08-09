'use strict';

define([
    'jquery',
    'underscore',
    'pim/form',
    'text!pim/template/export/product/edit/content/data/validation'

], function ($, _, BaseForm, template) {
    return BaseForm.extend({
        template: _.template(template),
        errors: [],

        /**
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));
            this.listenTo(
                this.getRoot(),
                'pim_enrich:form:export:validation_error',
                this.setValidationErrors.bind(this)
            );

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        setValidationErrors: function (errors) {
            this.errors = errors;
        },

        /**
         * Adds the extension to filters.
         * If there is an error for the current filter, we add an element to it.
         *
         * @param {Object} event
         */
        addFilterExtension: function (event) {
            var filter = event.filter;

            if (undefined !== this.errors.data &&
                undefined !== this.errors.data[filter.getField()]
            ) {
                var content = $(this.template({error: this.errors.data[filter.getField()]}));

                event.filter.addElement(
                    'below-input',
                    'validation',
                    content
                );
            }
        }
    });
});
