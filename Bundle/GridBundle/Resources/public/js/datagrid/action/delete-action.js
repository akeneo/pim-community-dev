/* global define */
define(['underscore', 'oro/messenger', 'oro/translator', 'oro/delete-confirmation', 'oro/modal', 'oro/grid/model-action'],
    function(_, messenger, __, DeleteConfirmation, Modal, ModelAction) {
        'use strict';

        /**
         * Delete action with confirm dialog, triggers REST DELETE request
         *
         * @export  oro/grid/delete-action
         * @class   oro.grid.DeleteAction
         * @extends oro.grid.ModelAction
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
                        var messageText = __('Item deleted');
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
                        content: __('Are you sure you want to delete this item?')
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
                        content: __('Cannot delete item.'),
                        cancelText: false
                    });
                }
                return this.errorModal;
            }
        });
    });
