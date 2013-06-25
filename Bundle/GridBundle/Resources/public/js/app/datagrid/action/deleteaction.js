var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Delete action with confirm dialog, triggers REST DELETE request
 *
 * @class   Oro.Datagrid.Action.DeleteAction
 * @extends Oro.Datagrid.Action.ModelAction
 */
Oro.Datagrid.Action.DeleteAction = Oro.Datagrid.Action.ModelAction.extend({

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
                var messageText = Translator.get('Item was deleted');
                if (!_.isUndefined(Oro.NotificationFlashMessage)) {
                    Oro.NotificationFlashMessage('success', messageText);
                } else {
                    alert(messageText);
                }
            }
        });
    },

    /**
     * Get view for confirm modal
     *
     * @return {Oro.BootstrapModal}
     */
    getConfirmDialog: function() {
        if (!this.confirmModal) {
            this.confirmModal = new Oro.BootstrapModal({
                title: Translator.get('Delete Confirmation'),
                content: Translator.get('Are you sure you want to delete this item?'),
                okText: 'Yes, Delete',
                allowCancel: 'false'
            });
            this.confirmModal.on('ok', _.bind(this.doDelete, this));
        }
        return this.confirmModal;
    },

    /**
     * Get view for error modal
     *
     * @return {Oro.BootstrapModal}
     */
    getErrorDialog: function() {
        if (!this.errorModal) {
            this.confirmModal = new Oro.BootstrapModal({
                title: 'Delete Error',
                content: 'Cannot delete item.',
                cancelText: false
            });
        }
        return this.confirmModal;
    }
});
