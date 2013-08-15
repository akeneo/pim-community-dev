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
    /** @property {String} */
    name: null,

    /** @property {Oro.Datagrid.Grid} */
    datagrid: null,

    /** @property {Backbone.BootstrapModal} */
    confirmModal: undefined,

    /** @property {string} */
    route: null,

    /** @property {string} */
    identifierFieldName: 'id',

    /** @property {Object} */
    defaultMessages: {
        confirm_title: _.__('Mass Action Confirmation'),
        confirm_content: _.__('Are you sure you want to do this?'),
        confirm_ok: _.__('Yes, do it'),
        success: _.__('Mass action was successfully performed.'),
        error: _.__('Mass action was not performed.'),
        empty_selection: _.__('Please, select items to perform mass action.')
    },

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Oro.Datagrid.Grid} options.datagrid
     * @throws {TypeError} If datagrid is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.datagrid) {
            throw new TypeError("'datagrid' is required");
        }
        this.datagrid = options.datagrid;

        this.messages = this.messages || {};

        _.defaults(this.messages, this.defaultMessages);

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Ask a confirmation and execute mass action.
     */
    execute: function() {
        var selectionState = this.datagrid.getSelectionState();
        if (_.isEmpty(selectionState.selectedModels) && selectionState.inset) {
            Oro.NotificationFlashMessage('warning', this.messages.empty_selection);
        } else {
            this.getConfirmDialog().open();
        }
    },

    /**
     * Perform mass action
     */
    doAction: function() {
        var self = this;
        this.datagrid.showLoading();
        $.ajax({
            url: this._getActionUrl(),
            data: this._getActionParams(),
            context: this,
            dataType: 'json',
            error: function (jqXHR, textStatus, errorThrown) {
                Oro.BackboneError.Dispatch(null, jqXHR);
                this.datagrid.hideLoading();
            },
            success: function (data, textStatus, jqXHR) {
                this.datagrid.hideLoading();
                this.datagrid.collection.fetch();
                this.datagrid.resetSelectionState();
                var defaultMessage = data.successful ? this.messages.success : this.messages.error;
                Oro.NotificationFlashMessage(
                    data.successful ? 'success' : 'error',
                    data.message ? data.message : defaultMessage
                );
            }
        });
    },

    /**
     * Get action url
     *
     * @return {String}
     * @private
     */
    _getActionUrl: function() {
        return Routing.generate(this.route, {gridName: this.datagrid.name, actionName: this.name});
    },

    /**
     * Get action parameters
     *
     * @returns {Object}
     * @private
     */
    _getActionParams: function() {
        var selectionState = this.datagrid.getSelectionState();
        var collection = this.datagrid.collection;
        var idValues = _.map(selectionState.selectedModels, function(model) {
            return model.get(this.identifierFieldName)
        }, this);

        var params = {
            inset: selectionState.inset ? 1 : 0,
            values: idValues.join(',')
        }

        params = collection.processFiltersParams(params, null, 'filters');

        return params;
    },

    /**
     * Get view for confirm modal
     *
     * @return {Oro.BootstrapModal}
     */
    getConfirmDialog: function() {
        if (!this.confirmModal) {
            this.confirmModal = new Oro.BootstrapModal({
                title: this.messages.confirm_title,
                content: this.messages.confirm_content,
                okText: this.messages.confirm_ok
            });
            this.confirmModal.on('ok', _.bind(this.doAction, this));
        }
        return this.confirmModal;
    }
});
