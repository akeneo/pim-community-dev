/* global define */
define(['underscore', 'oro/messenger', 'oro/translator', 'oro/modal', 'oro/grid/abstract-action'],
function(_, messenger, __, Modal, AbstractAction) {
    'use strict';

    /**
     * Basic mass action class.
     *
     * @export  oro/grid/mass-action
     * @class   oro.grid.MassAction
     * @extends oro.grid.AbstractAction
     */
    return AbstractAction.extend({
        /** @property {Object} */
        defaultMessages: {
            confirm_title: __('Mass Action Confirmation'),
            confirm_content: __('Are you sure you want to do this?'),
            confirm_ok: __('Yes, do it'),
            success: __('Mass action performed.'),
            error: __('Mass action is not performed.'),
            empty_selection: __('Please, select items to perform mass action.')
        },

        initialize: function(options) {
            AbstractAction.prototype.initialize.apply(this, arguments);
            this.route_parameters = _.extend(this.route_parameters, {gridName: this.datagrid.name, actionName: this.name});
        },

        /**
         * Ask a confirmation and execute mass action.
         */
        execute: function() {
            var selectionState = this.datagrid.getSelectionState();
            if (_.isEmpty(selectionState.selectedModels) && selectionState.inset) {
                messenger.notificationFlashMessage('warning', this.messages.empty_selection);
            } else {
                AbstractAction.prototype.execute.call(this);
            }
        },

        /**
         * Get action parameters
         *
         * @returns {Object}
         * @private
         */
        getActionParameters: function() {
            var selectionState = this.datagrid.getSelectionState();
            var collection = this.datagrid.collection;
            var idValues = _.map(selectionState.selectedModels, function(model) {
                return model.get(this.identifierFieldName)
            }, this);

            var params = {
                inset: selectionState.inset ? 1 : 0,
                values: idValues.join(',')
            };

            params = collection.processFiltersParams(params, null, 'filters');

            return params;
        },

        _onAjaxSuccess: function(data, textStatus, jqXHR) {
            this.datagrid.resetSelectionState();
            AbstractAction.prototype._onAjaxSuccess.apply(this, arguments);
        },

        /**
         * Get view for confirm modal
         *
         * @return {oro.Modal}
         */
        getConfirmDialog: function(callback) {
            return new Modal({
                title: this.messages.confirm_title,
                content: this.messages.confirm_content,
                okText: this.messages.confirm_ok
            }).on('ok', callback);
        }
    });
});
