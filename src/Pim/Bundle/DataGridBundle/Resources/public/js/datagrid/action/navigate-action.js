/* global define */
import _ from 'underscore';
import mediator from 'oro/mediator';
import ModelAction from 'oro/datagrid/model-action';
import router from 'pim/router';
    

    /**
     * Navigate action. Changes window location to url, from getLink method
     *
     * @export  oro/datagrid/navigate-action
     * @class   oro.datagrid.NavigateAction
     * @extends oro.datagrid.ModelAction
     */
    export default ModelAction.extend({

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
            ModelAction.prototype.initialize.apply(this, arguments);

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
            router.redirect(this.getLink());
        },

        /**
         * Trigger global event
         *
         * @private
         */
        _preExecuteSubscriber: function(action, options) {
            mediator.trigger('grid_action:navigateAction:preExecute', action, options);
        }
    });

