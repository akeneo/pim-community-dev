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

    /** @property {string} */
    route: null,

    /** @property {Object} */
    route_parameters: null,

    /** @property {Boolean} */
    confirmation: false,

    /** @property {String} */
    frontend_type: null,

    /** @property {Object} */
    frontend_options: null,

    /** @property {string} */
    identifierFieldName: 'id',

    messages: {},

    dispatched: false,

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
            var eventName = this.getEventName();
            Oro.Events.once(eventName, this.executeConfiguredAction, this);
            this._confirmationExecutor(
                _.bind(
                    function() {Oro.Events.trigger(eventName, this);},
                    this
                )
            );
        }
    },

    getEventName: function() {
        return 'grid_action_execute:' + this.datagrid.name + ':' + this.name;
    },

    executeConfiguredAction: function(action) {
        if (action.frontend_type == 'ajax') {
            this._handleAjax(action);
        } else if (action.frontend_type == 'redirect') {
            this._handleRedirect(action);
        } else if (Oro.widget.Manager.isSupportedType(action.frontend_type)) {
            this._handleWidget(action);
        }
    },

    _confirmationExecutor: function(callback) {
        if (this.confirmation) {
            this.getConfirmDialog(callback).open();
        } else {
            callback();
        }
    },

    _handleWidget: function(action) {
        if (action.dispatched) {
            return;
        }
        action.frontend_options.url = action.frontend_options.url || this._getActionUrl();
        action.frontend_options.title = action.frontend_options.title || this.label;
        Oro.widget.Manager.createWidget(action.frontend_type, action.frontend_options).render();
    },

    _handleRedirect: function(action) {
        if (action.dispatched) {
            return;
        }
        var url = action._getActionUrl(this._getActionParams());
        if (Oro.hashNavigationEnabled()) {
            Oro.hashNavigationInstance.processRedirect({
                fullRedirect: false,
                location: url
            });
        } else {
            location.href = url;
        }
    },

    _handleAjax: function(action) {
        if (action.dispatched) {
            return;
        }
        action.datagrid.showLoading();
        $.ajax({
            url: action._getActionUrl(),
            data: action._getActionParams(),
            context: action,
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
    _getActionUrl: function(parameters) {
        if (_.isUndefined(parameters)) {
            parameters = {};
        }
        return Routing.generate(
            this.route,
            _.extend(
                {gridName: this.datagrid.name, actionName: this.name},
                this.route_parameters,
                parameters
            )
        );
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
        };

        params = collection.processFiltersParams(params, null, 'filters');

        return params;
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
