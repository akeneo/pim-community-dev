/* global define */
define([
        'underscore',
        'oro/messenger',
        'oro/translator',
        'pim/dialog',
        'oro/modal',
        'oro/datagrid/model-action',
        'oro/mediator',
        'pim/user-context'
    ],
    function(_, messenger, __, Dialog, Modal, ModelAction, mediator, userContext) {
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
             * Initialize view
             *
             * @param {Object} options
             * @param {Backbone.Model} options.model Optional parameter
             * @throws {TypeError} If model is undefined
             */
            initialize: function(options) {
                options = options || {};

                this.gridName = options.datagrid.name;

                ModelAction.prototype.initialize.apply(this, arguments);
            },

            /**
             * Execute delete model
             */
            execute: function() {
                this.getConfirmDialog();
            },

            /**
             * Confirm delete item
             */
            doDelete: function() {
                this.model.id = true;
                this.model.destroy({
                    url: this.getLink(),
                    wait: true,
                    error: function() {
                        this.getErrorDialog().open();
                    }.bind(this),
                    success: function() {
                        var messageText = __('flash.' + this.getEntityHint() + '.removed');
                        messenger.notify('success', messageText);
                        userContext.initialize();

                        mediator.trigger('datagrid:doRefresh:' + this.gridName);
                    }.bind(this)
                });
            },

            /**
             * Get view for confirm modal
             *
             * @return {oro.Modal}
             */
            getConfirmDialog: function() {
                this.confirmModal = Dialog.confirmDelete(
                    __(`confirmation.remove.${this.getEntityHint()}`),
                    __('pim_enrich.confirmation.delete_item'),
                    this.doDelete.bind(this),
                    this.getEntityHint(true)
                );

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

            getEntityHint: function(plural) {
                const entityHint = this.datagrid && this.datagrid.entityHint ? this.datagrid.entityHint : 'item';

                if (plural) {
                    return `${entityHint}s`.split('_').join(' ');
                }

                return entityHint.split(' ').join('_');
            }
        });
    }
);
