define(
    ['jquery', 'underscore', 'backgrid'],
    function($, _, Backgrid) {
        'use strict';

        /**
         * Cell for grid actions
         *
         * @export  oro/datagrid/action-cell
         * @class   oro.datagrid.ActionCell
         * @extends Backgrid.Cell
         */
        return Backgrid.Cell.extend({

            /** @property */
            className: 'AknGrid-bodyCell AknGrid-bodyCell--actions action-cell',

            /** @property {Array} */
            actions: undefined,

            /** @property {Array} */
            launchers: undefined,

            /**
             * Initilize cell actions and launchers
             */
            initialize: function() {
                Backgrid.Cell.prototype.initialize.apply(this, arguments);
                this.actions = this.createActions();

                this.launchers = this.createLaunchers();
            },

            /**
             * Creates actions
             *
             * @return {Array}
             */
            createActions: function() {
                var result = [];

                var actions = this.column.get('actions');
                var actionConfiguration = this.model.get('action_configuration');
                _.each(actions, function(action, name) {
                    // filter available actions for current row
                    if (_.isUndefined(actionConfiguration) ||
                        _.isUndefined(actionConfiguration[name]) ||
                        actionConfiguration[name]) {
                        if (action.prototype.hidden !== true) {
                            result.push(this.createAction(action));
                        }
                    }
                }, this);

                return result;
            },

            /**
             * Creates action
             *
             * @param {Function} ActionPrototype
             * @protected
             */
            createAction: function(ActionPrototype) {
                return new ActionPrototype({
                    model: this.model,
                    datagrid: this.column.get('datagrid')
                });
            },

            /**
             * Creates actions launchers
             *
             * @protected
             */
            createLaunchers: function() {
                return _.map(this.actions, function(action) {
                    var launcherClass = action.launcherOptions.className;
                    if (_.isUndefined(launcherClass) || ('' === launcherClass) || ('no-hash' === launcherClass)) {
                        launcherClass = 'AknIconButton AknIconButton--small AknIconButton--grey';
                    }
                    return action.createLauncher({
                        className: launcherClass + ' AknButtonList-item'
                    });
                });
            },

            /**
             * Render cell with actions
             */
            render: function () {
                this.$el.empty();
                var iconsList = $('<div>').addClass('AknButtonList AknButtonList--right AknButtonList--expanded');
                if (!_.isEmpty(this.launchers)) {
                    _.each(this.launchers, function(launcher) {
                        iconsList.append(launcher.render().$el);
                    }, this);
                }
                this.$el.append(iconsList);

                return this;
            }
        });
    }
);
