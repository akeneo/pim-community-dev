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

        return this;
    },

    /**
     * Enable
     *
     * @return {*}
     */
    enable: function() {
        _.each(this.launchers, function(launcher) {
            launcher.enable();
        });

        return this;
    }
});
