/* global define */
define(['underscore', 'backbone', 'pim/template/datagrid/actions-group'],
function(_, Backbone, groupTemplate) {
    'use strict';

    /**
     * Panel with action buttons
     *
     * @export  oro/datagrid/actions-panel
     * @class   oro.datagrid.ActionsPanel
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Array} */
        actionsGroups: [],

        /** @property {Array.<oro.datagrid.AbstractAction>} */
        actions: [],

        /** @property {Array.<oro.datagrid.ActionLauncher>} */
        launchers: [],

        /** @property {Function} */
        groupTemplate: _.template(groupTemplate),

        /**
         * Initialize view
         *
         * @param {Object} options
         * @param {Array} [options.actions] List of actions
         */
        initialize: function(options) {
            options = options || {};

            if (options.actionsGroups) {
                this.actionsGroups = options.actionsGroups;
            }

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

            var simpleLaunchers = _.filter(this.launchers, function (launcher) {
                return undefined === launcher.getGroup();
            });
            var groupedLaunchers = _.filter(this.launchers, function (launcher) {
                return undefined !== launcher.getGroup();
            });

            if (simpleLaunchers.length) {
                var $container = $('<div class="AknGridToolbar-actionButton"></div>');
                _.each(simpleLaunchers, function (launcher) {
                    $container.append(launcher.render().$el);
                }, this);

                this.$el.append($container);
            }

            if (groupedLaunchers.length) {
                this.renderGroupedLaunchers(groupedLaunchers);
            }

            return this;
        },

        /**
         * Render launchers belonging to actions groups
         *
         * @param {Array} launchers
         *
         * @return {*}
         */
        renderGroupedLaunchers: function (launchers) {
            var groupedLaunchers = _.groupBy(launchers, function (launcher) { return launcher.getGroup() });
            var activeGroups = _.pick(this.actionsGroups, _.keys(groupedLaunchers));

            _.each(activeGroups, function (group, name) {
                this.$el.append(
                    this.groupTemplate({
                        classname: this.getGroupClassname(name),
                        group: group
                    })
                );
            }.bind(this));

            _.each(groupedLaunchers, function (groupLaunchers, groupName) {
                var $dropdown = this.$el.find('.' + this.getGroupClassname(groupName) + ' .AknDropdown-menu');
                _.each(groupLaunchers, function (launcher) {
                    $dropdown.append(launcher.renderAsListItem().$el);
                });
            }.bind(this));

            return this;
        },

        /**
         * Build the class name for the specified action group
         *
         * @param {String} groupName
         *
         * @return {String}
         */
        getGroupClassname: function (groupName) {
            return groupName.replace('_', '-') + '-actions-group';
        },

        /**
         * Set actions
         *
         * @param {Array.<oro.datagrid.AbstractAction>} actions
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
         * @param {oro.datagrid.AbstractAction} action
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
});
