define(['underscore', 'oro/translator', 'oro/modal'],
    function (_, __, Modal) {
        'use strict';

        /**
         * Revoke confirmation dialog
         *
         * @export  oro/revoke-confirmation
         * @class   oro.RevokeConfirmation
         * @extends oro.Modal
         */
        return Modal.extend({
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                options = _.extend({
                    title: __('Revoke Confirmation'),
                    okText: __('Yes, revoke'),
                    cancelText: __('Cancel')
                }, options);

                arguments[0] = options;
                Modal.prototype.initialize.apply(this, arguments);
            }
        });
    });
