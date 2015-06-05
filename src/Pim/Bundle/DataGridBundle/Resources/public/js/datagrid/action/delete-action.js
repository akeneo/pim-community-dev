/* global define */
define(['underscore', 'oro/messenger', 'oro/translator', 'oro/delete-confirmation', 'oro/modal', 'oro/datagrid/model-action'],
    function(_, messenger, __, DeleteConfirmation, Modal, ModelAction) {
        'use strict';

        /**
         * Delete action with confirm dialog, triggers REST DELETE request
         *
         * @export  oro/datagrid/delete-action
         * @class   oro.datagrid.DeleteAction
         * @extends oro.datagrid.ModelAction
         */
        return ModelAction.extend({

            /** @type oro.Modal */
            errorModal: undefined,

            /** @type oro.Modal */
            confirmModal: undefined,

            /**
             * Execute delete model
             */
            execute: function() {
                this.getConfirmDialog().open();
            },

            /**
             * Confirm delete item
             */
            doDelete: function() {
                var self = this;
                this.model.destroy({
                    url: this.getLink(),
                    wait: true,
                    error: function() {
                        self.getErrorDialog().open();
                    },
                    success: function() {
                        var messageText = __('flash.' + self.getEntityHint() + '.removed');
                        messenger.notificationFlashMessage('success', messageText);
                    }
                });
            },

            /**
             * Get view for confirm modal
             *
             * @return {oro.Modal}
             */
            getConfirmDialog: function() {
                if (!this.confirmModal) {
                    this.confirmModal = new DeleteConfirmation({
                        content: __('confirmation.remove.' + this.getEntityHint())
                    });
                    this.confirmModal.on('ok', _.bind(this.doDelete, this));
                }
                return this.confirmModal;
            },

            /**
             * Get view for error modal
             *
             * @return {oro.Modal}
             */
            getErrorDialog: function() {
                if (!this.errorModal) {
                    this.errorModal = new Modal({
                        title: __('Delete Error'),
                        content: __('error.removing.' + this.getEntityHint()),
                        cancelText: false
                    });
                }
                return this.errorModal;
            },

            getEntityHint: function() {
                return this.datagrid && this.datagrid.entityHint ? this.datagrid.entityHint : 'item';
            }
        });
    }
);
