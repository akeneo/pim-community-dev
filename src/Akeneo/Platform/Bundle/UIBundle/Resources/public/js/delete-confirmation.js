define(
    [
        'underscore',
        'oro/translator',
        'oro/modal',
    ], function (
        _,
        __,
        Modal
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
        /**
         * @param {Object} options
         */
        initialize: function (options) {
            options = _.extend({
                title: __('pim_common.confirm_deletion'),
                okText: __('pim_common.ok'),
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
