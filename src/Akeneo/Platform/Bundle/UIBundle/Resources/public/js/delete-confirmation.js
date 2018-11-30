define(
    [
        'underscore',
        'oro/translator',
        'oro/modal',
        'pim/template/common/modal-with-illustration'
    ], function (
        _,
        __,
        Modal,
        confirmModalTemplate
    ) {
    'use strict';

    /**
     * Confirm deletion dialog
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
                title: __('pim_common.confirm_deletion'),
                okText: __('pim_common.ok'),
                template: this.confirmModalTemplate,
                buttonClass: 'AknButton--important',
                illustrationClass: 'delete',
                cancelText: __('pim_common.cancel'),
            }, options);

            arguments[0] = options;

            this.$el.addClass('modal--fullPage');

            Modal.prototype.initialize.apply(this, arguments);
        }
    });
});
