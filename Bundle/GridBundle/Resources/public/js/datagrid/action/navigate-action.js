var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Navigate action. Changes window location to url, from getLink method
 *
 * @class   Oro.Datagrid.Action.NavigateAction
 * @extends Oro.Datagrid.Action.ModelAction
 */
Oro.Datagrid.Action.NavigateAction = Oro.Datagrid.Action.ModelAction.extend({

    /**
     * If `true` then created launcher will be complete clickable link,
     * If `false` redirection will be delegated to execute method.
     *
     * @property {Boolean}
     */
    useDirectLauncherLink: true,

    /**
     * Initialize launcher options with url
     *
     * @param {Object} options
     * @param {Boolean} options.useDirectLauncherLink
     */
    initialize: function(options) {
        Oro.Datagrid.Action.ModelAction.prototype.initialize.apply(this, arguments);

        if (options.useDirectLauncherLink) {
            this.useDirectLauncherLink = options.useDirectLauncherLink;
        }

        this.on('preExecute', _.bind(this._preExecuteSubscriber, this));

        if (this.useDirectLauncherLink) {
            this.launcherOptions = _.extend({
                link: this.getLink(),
                runAction: false
            }, this.launcherOptions);
        }
    },

    /**
     * Execute redirect
     */
    execute: function() {
        window.location.href = this.getLink();
    },

    /**
     * Trigger global event
     *
     * @private
     */
    _preExecuteSubscriber: function(action, options) {
        Oro.Events.trigger('grid_action:navigateAction:preExecute', action, options);
    }
});
