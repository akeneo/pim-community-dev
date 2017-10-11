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
                const entityType = this.getEntityHint().split(' ').join('_')
                this.confirmModal = Dialog.confirm(
                    __(`confirmation.remove.${entityType}`),
                    __('Confirm revocation'),
                    this.doDelete.bind(this),
                    this.getEntityHint(true)
                )

                return this.confirmModal;
            },
        });
    }
);
