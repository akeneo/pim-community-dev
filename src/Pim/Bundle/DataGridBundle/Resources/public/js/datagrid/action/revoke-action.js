/* global define */
define([
        'underscore',
        'oro/translator',
        'oro/datagrid/delete-action',
        'oro/revoke-confirmation'
    ],
    function(_, __, DeleteAction, RevokeConfirmation) {
        'use strict';

        /**
         * Revoke action with confirm dialog, triggers REST DELETE request
         *
         * @export  oro/datagrid/revoke-action
         * @class   oro.datagrid.RevokeAction
         * @extends oro.datagrid.DeleteAction
         */
        return DeleteAction.extend({
            getConfirmDialog: function() {
                if (!this.confirmModal) {
                    this.confirmModal = new RevokeConfirmation({
                        content: __('confirmation.remove.' + this.getEntityHint())
                    });
                    this.confirmModal.on('ok', _.bind(this.doDelete, this));
                }
                return this.confirmModal;
            },
        });
    }
);
