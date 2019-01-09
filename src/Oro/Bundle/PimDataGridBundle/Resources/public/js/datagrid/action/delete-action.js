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
                    error: function(element, response) {
                        let contentType = response.getResponseHeader('content-type');
                        let message = '';
                        //Need to check if it is a json because the backend can return an error
                        if (contentType.indexOf("application/json") !== -1) {
                            const decodedResponse = JSON.parse(response.responseText);
                            if (undefined !== decodedResponse.message) {
                                message = decodedResponse.message
                            }
                        }

                        this.getErrorDialog(message).open();
                    }.bind(this),
                    success: function() {
                        var messageText = __('pim_enrich.entity.' + this.getEntityCode() + '.flash.delete.success');
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
                const entityCode = this.getEntityCode();

                this.confirmModal = Dialog.confirmDelete(
                    __(`pim_enrich.entity.${entityCode}.module.delete.confirm`),
                    __('pim_common.confirm_deletion'),
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
            getErrorDialog: function(response) {
                let message = '';

                if (typeof response === 'string') {
                    message = response;
                } else {
                    try {
                        message = JSON.parse(response).message;
                    } catch(e) {
                        message = __('pim_enrich.entity.' + this.getEntityHint() + '.flash.delete.fail');
                    }
                }

                this.errorModal = new Modal({
                    title: __('pim_datagrid.delete_error.title'),
                    content: '' === message ?
                        __('pim_enrich.entity.' + this.getEntityHint() + '.flash.delete.fail') :
                        message,
                    buttonClass: 'AknButton--important',
                    illustrationClass: 'delete',
                    cancelText: '',
                });

                return this.errorModal;
            }
        });
    }
);
