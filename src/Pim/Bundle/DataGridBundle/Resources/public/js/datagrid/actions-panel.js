/* global define */
define(['underscore', 'backbone', 'pim/template/datagrid/actions-group', 'pim/form', 'oro/mediator'],
function(_, Backbone, groupTemplate, BaseForm, mediator) {
    'use strict';

    /**
     * Panel with action buttons
     *
     * @export  oro/datagrid/actions-panel
     * @class   oro.datagrid.ActionsPanel
     * @extends BaseForm
     */
    return BaseForm.extend({
        /** @property {Array} */
        actionsGroups: [],

        /** @property {Array.<oro.datagrid.AbstractAction>} */
        actions: [],

        /** @property {Array.<oro.datagrid.ActionLauncher>} */
        launchers: [],

        className: 'AknGridToolbar-left mass-actions-panel',

        /** @property {Function} */
        groupTemplate: _.template(groupTemplate),

        /**
         * Initialize view
         *
         * @param {Object} options
         * @param {Array} [options.actions] List of actions
         */
        initialize: function() {
            mediator.on('grid_load:complete', this.setupActions.bind(this));
        },

        /**
         * Get the action options from the datagrid
         */
        setupActions: function(collection, datagrid) {
            this.actionsGroups = datagrid.massActionsGroups;
            this.setActions(datagrid.massActions, datagrid);
            this.renderActions();

            return BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * Renders panel
         *
         * @return {*}
         */
        renderActions: function () {
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
        setActions: function(actions, datagrid) {
            this.actions = [];
            this.launchers = [];
            _.each(actions, function(action) {
                this.addAction(action, datagrid);
            }, this);
        },

        /**
         * Adds action to toolbar
         *
         * @param {oro.datagrid.AbstractAction} action
         */
        addAction: function(Action, datagrid) {
            const actionModule = new Action({ datagrid });
            this.actions.push(actionModule);
            this.launchers.push(actionModule.createLauncher());
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
