var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Abstract action class. Subclasses should override execute method which is invoked when action is running.
 *
 * Triggers events:
 *  - "preExecute" before action is executed
 *  - "postExecute" after action is executed
 *
 * @class   Oro.Datagrid.Action.AbstractAction
 * @extends Backbone.View
 */
Oro.Datagrid.Action.AbstractAction = Backbone.View.extend({
    /** @property {Function} */
    launcherPrototype: Oro.Datagrid.Action.Launcher,

    /** @property {Object} */
    launcherOptions: undefined,

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Object} [options.launcherOptions] Options for new instance of launcher object
     */
    initialize: function(options) {
        options = options || {};

        if (options.launcherOptions) {
            this.launcherOptions = _.extend({}, this.launcherOptions, options.launcherOptions);
        }

        this.launcherOptions = _.extend({
            action: this
        }, this.launcherOptions);

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Creates launcher
     *
     * @param {Object} options Launcher options
     * @return {Oro.Datagrid.Action.Launcher}
     */
    createLauncher: function(options) {
        options = options || {};
        if (_.isUndefined(options.icon) && !_.isUndefined(this.icon)) {
            options.icon = this.icon;
        }
        _.defaults(options, this.launcherOptions);
        return new (this.launcherPrototype)(options);
    },

    /**
     * Run action
     */
    run: function() {
        var options = {
            doExecute: true
        };
        this.trigger('preExecute', this, options);
        if (options.doExecute) {
            this.execute();
            this.trigger('postExecute', this, options);
        }
    },

    /**
     * Execute action
     */
    execute: function() {
        throw new Error("Method execute is abstract and must be implemented");
    }
});
