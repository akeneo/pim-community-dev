'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'text!pim/template/export/product/edit/content/data/validation'

], function ($, _, __, BaseForm, template) {
    return BaseForm.extend({
        template: _.template(template),

        /**
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:filter:extension:add', this.addFilterExtension.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Adds the extension to filters.
         * If the translation is not here the tooltip won't be displayed at all.
         *
         * @param {Object} event
         */
        addFilterExtension: function (event) {
            var filter = event.filter;

            if (_.isEmpty(filter.validationErrors)) {
                return false;
            }

            var $content = $(this.template({errors: filter.validationErrors}));

            event.filter.addElement(
                'below-input',
                'validation',
                $content
            );
        }
    });
});
