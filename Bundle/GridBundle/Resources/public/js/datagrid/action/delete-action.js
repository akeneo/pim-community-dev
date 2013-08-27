/* global define */
define(['underscore', 'oro/app', 'oro/translator', 'oro/bootstrap-modal', 'oro/datagrid/model-action'],
function(_, app, __, BootstrapModal, ModelAction) {
    'use strict';

    /**
     * Delete action with confirm dialog, triggers REST DELETE request
     *
     * @export  oro/datagrid/delete-action
     * @class   oro.datagrid.DeleteAction
     * @extends oro.datagrid.ModelAction
     */
    return ModelAction.extend({

        /** @property Backbone.BootstrapModal */
        errorModal: undefined,

        /** @property Backbone.BootstrapModal */
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
                    var messageText = __('Item was deleted');
                    app.NotificationFlashMessage('success', messageText);
                }
            });
        },

        /**
         * Get view for confirm modal
         *
         * @return {oro.BootstrapModal}
         */
        getConfirmDialog: function() {
            if (!this.confirmModal) {
                this.confirmModal = new BootstrapModal({
                    title: __('Delete Confirmation'),
                    content: __('Are you sure you want to delete this item?'),
                    okText: __('Yes, Delete')
                });
                this.confirmModal.on('ok', _.bind(this.doDelete, this));
            }
            return this.confirmModal;
        },

        /**
         * Get view for error modal
         *
         * @return {oro.BootstrapModal}
         */
        getErrorDialog: function() {
            if (!this.errorModal) {
                this.errorModal = new BootstrapModal({
                    title: __('Delete Error'),
                    content: __('Cannot delete item.'),
                    cancelText: false
                });
            }
            return this.errorModal;
        }
    });
});
