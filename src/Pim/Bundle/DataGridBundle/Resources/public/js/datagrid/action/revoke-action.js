/* global define */
define([
        'underscore',
        'oro/translator',
        'oro/datagrid/delete-action',
        'pim/dialog'
    ],
    function(_, __, DeleteAction, Dialog) {
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
                this.confirmModal = Dialog.confirm(
                    __('confirmation.remove.' + this.getEntityHint()),
                    __('Revoke confirmation'),
                    this.doDelete.bind(this),
                    'API Connections',
                    null,
                    __('Revoke')
                )

                return this.confirmModal;
            },
        });
    }
);
