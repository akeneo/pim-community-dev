/* global define */
define(['underscore', 'oro/translator', 'oro/modal'],
function(_, __, Modal) {
    'use strict';

    /**
     * Delete confirmation dialog
     *
     * @export  oro/delete-confirmation
     * @class   oro.DeleteConfirmation
     * @extends oro.Modal
     */
    return Modal.extend({
        /**
         * @param {Object} options
         */
        initialize: function(options) {
            options = _.extend({
                title: __('Delete Confirmation'),
                okText: __('Yes, Delete')
            }, options);

            arguments[0] = options;
            Modal.prototype.initialize.apply(this, arguments);
        }
    });
});
