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
                const entityCode = this.getEntityCode();

                this.confirmModal = Dialog.confirm(
                    __(`pim_enrich.entity.${entityCode}.module.revoke.confirm`),
                    __('pim_common.confirm_revocation'),
                    this.doDelete.bind(this),
                    this.getEntityHint(true)
                );

                return this.confirmModal;
            },
        });
    }
);
