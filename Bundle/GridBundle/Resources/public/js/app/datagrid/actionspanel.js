var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Panel with action buttons
 *
 * @class   Oro.Datagrid.ActionsPanel
 * @extends Backbone.View
 */
Oro.Datagrid.ActionsPanel = Backbone.View.extend({
    /** @property String */
    className: 'btn-group',

    /** @property {Oro.Datagrid.Action.AbstractAction[]} */
    actions: [],

    /** @property {Oro.Datagrid.Action.Launcher[]} */
    launchers: [],

    /** @property */
    enabled: true,

    /** @property */
    enabledNow: true,

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Array} [options.actions] List of actions
     */
    initialize: function(options) {
        options = options || {};

        if (options.actions) {
            this.setActions(options.actions);
        }

        this.enabled = options.enable != false;
        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Renders panel
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        _.each(this.launchers, function(launcher) {
            this.$el.append(launcher.render().$el);
        }, this);

        return this;
    },

    /**
     * Set actions
     *
     * @param {Oro.Datagrid.Action.AbstractAction[]} actions
     */
    setActions: function(actions) {
        this.actions = [];
        this.launchers = [];
        _.each(actions, function(action) {
            this.addAction(action);
        }, this);

        if (this.enabled == false) {
            this.disable();
        }
    },

    /**
     * Adds action to toolbar
     *
     * @param {Oro.Datagrid.Action.AbstractAction} action
     */
    addAction: function(action) {
        this.actions.push(action);
        this.launchers.push(action.createLauncher());
    },

    /**
     * Disable
     *
     * @return {*}
     */
    disable: function() {
        _.each(this.launchers, function(launcher) {
            launcher.disable();
        });
        this.enabledNow = false;

        return this;
    },

    /**
     * Enable
     *
     * @return {*}
     */
    enable: function(force) {
        if (force == undefined && !this.enabled) {
            return false;
        }

        _.each(this.launchers, function(launcher) {
            launcher.enable();
        });
        this.enabledNow = true;

        return this;
    }
});
