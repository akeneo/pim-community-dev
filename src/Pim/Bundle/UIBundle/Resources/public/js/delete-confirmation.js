define(['underscore', 'oro/translator', 'oro/modal', 'pim/template/grid/mass-actions-confirm'],
function (_, __, Modal, confirmModalTemplate) {
    'use strict';

    /**
     * Delete confirmation dialog
     *
     * @export  oro/delete-confirmation
     * @class   oro.DeleteConfirmation
     * @extends oro.Modal
     */
    return Modal.extend({
        confirmModalTemplate: _.template(confirmModalTemplate),
        /**
         * @param {Object} options
         */
        initialize: function (options) {
            options = _.extend({
                title: __('Delete Confirmation'),
                okText: __('Yes, Delete'),
                cancelText: __('Cancel'),
                template: this.confirmModalTemplate,
                type: '',
                buttonClass: 'AknButton--important'
            }, options);

            arguments[0] = options;

            this.$el.addClass('modal--fullPage');

            Modal.prototype.initialize.apply(this, arguments);
        }
    });
});
