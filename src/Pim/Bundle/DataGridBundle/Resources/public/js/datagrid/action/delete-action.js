define(
    ['underscore', 'oro/messenger', 'oro/translator', 'oro/delete-confirmation', 'oro/modal', 'oro/datagrid/delete-action'],
    function(_, messenger, __, DeleteConfirmation, Modal, DeleteAction) {
        'use strict';

        /**
         * Override delete action to display more specific messages
         */
        return DeleteAction.extend({
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

            getConfirmDialog: function() {
                if (!this.confirmModal) {
                    this.confirmModal = new DeleteConfirmation({
                        content: __('confirmation.remove.' + this.getEntityHint())
                    });
                    this.confirmModal.on('ok', _.bind(this.doDelete, this));
                }
                return this.confirmModal;
            },

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
