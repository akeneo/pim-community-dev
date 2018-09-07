/* global define */

/**
 * Panel with action buttons
 *
 * @export  oro/datagrid/actions-panel
 * @class   oro.datagrid.ActionsPanel
 * @extends BaseForm
 */
define(
    [
        'underscore',
        'oro/translator',
        'backbone',
        'pim/template/datagrid/actions-group',
        'pim/form',
        'oro/mediator'
    ], function(
        _,
        __,
        Backbone,
        groupTemplate,
        BaseForm,
        mediator
    ) {
    'use strict';

    const ActionsPanel = BaseForm.extend({
        appendToGrid: false,

        /** @property {Array} */
        actionsGroups: [],

        /** @property {Array.<oro.datagrid.AbstractAction>} */
        actions: [],

        /** @property {Array.<oro.datagrid.ActionLauncher>} */
        launchers: [],

        className: 'AknButtonList mass-actions-panel',

        /** @property {Function} */
        groupTemplate: _.template(groupTemplate),

        /**
         * Initialize view
         *
         * @param {Object} options
         * @param {Array} [options.actions] List of actions
         */
        initialize: function(options) {
            this.appendToGrid = options.appendToGrid;
            this.gridElement = options.gridElement;

            mediator.once('grid_load:start', this.setupActions.bind(this));
            mediator.on('grid_load:complete', this.setupActions.bind(this));

            return BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * Get the action options from the datagrid
         */
        setupActions: function(collection, datagrid) {
            this.actionsGroups = datagrid.massActionsGroups;
            this.setActions(datagrid.massActions, datagrid);
            this.renderActions();
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
                _.each(simpleLaunchers, (launcher) => {
                    this.$el.append(launcher.render().$el);
                });
            }

            if (groupedLaunchers.length) {
                this.renderGroupedLaunchers(groupedLaunchers);
            }

            if (this.appendToGrid) {
                this.gridElement.prepend(this.$el);
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
                        __,
                        classname: this.getGroupClassname(name),
                        group: group
                    })
                );
            }.bind(this));

            _.each(groupedLaunchers, function (groupLaunchers, groupName) {
                const $dropdown = this.$el.find('.' + this.getGroupClassname(groupName) + ' .AknDropdown-menu');
                _.each(groupLaunchers, (launcher) => {
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

    ActionsPanel.init = (gridContainer, gridName) => {
        return new ActionsPanel({ appendToGrid: true, gridElement: $(gridContainer).find('.grid-container') });
    }

    return ActionsPanel;
});
