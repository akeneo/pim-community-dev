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
    messages: {
        confirm_title: _.__('Mass Action Confirmation'),
        confirm_content: _.__('Are you sure you want to do this?'),
        confirm_ok: _.__('Yes, do it')
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

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Ask a confirmation and execute mass action.
     */
    execute: function() {
        this.getConfirmDialog().open();
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
        var idValues = _.map(selectionState.selectedModels, function(model) {
            return model.get(this.identifierFieldName)
        }, this);

        return {
            inset: selectionState.inset ? 1 : 0,
            values: idValues.join(',')
        }
    },

    /**
     * Get view for confirm modal
     *
     * @return {Oro.BootstrapModal}
     */
    getConfirmDialog: function() {
        if (!this.confirmModal) {
            // TODO Replace translations with passed from configuration of mass action
            this.confirmModal = new Oro.BootstrapModal({
                title: this.messages.confirm_title,
                content: this.messages.confirm_content,
                okText: this.messages.confirm_ok,
                allowCancel: 'false'
            });
            this.confirmModal.on('ok', _.bind(this.doAction, this));
        }
        return this.confirmModal;
    }
});
