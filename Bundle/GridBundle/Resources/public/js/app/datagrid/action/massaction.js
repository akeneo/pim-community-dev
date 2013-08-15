var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Basic mass action class.
 *
 * @class   Oro.Datagrid.MassAction
 * @extends Oro.Datagrid.Action.AbstractAction
 */
Oro.Datagrid.Action.MassAction = Oro.Datagrid.Action.AbstractAction.extend({
    /** @property {Object} */
    defaultMessages: {
        confirm_title: _.__('Mass Action Confirmation'),
        confirm_content: _.__('Are you sure you want to do this?'),
        confirm_ok: _.__('Yes, do it'),
        success: _.__('Mass action was successfully performed.'),
        error: _.__('Mass action was not performed.'),
        empty_selection: _.__('Please, select items to perform mass action.')
    },

    initialize: function(options) {
        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
        this.route_parameters = _.extend(this.route_parameters, {gridName: this.datagrid.name, actionName: this.name});
    },

    /**
     * Ask a confirmation and execute mass action.
     */
    execute: function() {
        var selectionState = this.datagrid.getSelectionState();
        if (_.isEmpty(selectionState.selectedModels) && selectionState.inset) {
            Oro.NotificationFlashMessage('warning', this.messages.empty_selection);
        } else {
            Oro.Datagrid.Action.AbstractAction.prototype.execute.call(this);
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
        Oro.Datagrid.Action.AbstractAction.prototype._onAjaxSuccess.call(this, arguments);
    },

    /**
     * Get view for confirm modal
     *
     * @return {Oro.BootstrapModal}
     */
    getConfirmDialog: function(callback) {
        return new Oro.BootstrapModal({
            title: this.messages.confirm_title,
            content: this.messages.confirm_content,
            okText: this.messages.confirm_ok
        }).on('ok', callback);
    }
});
