 /* global define */
define(['jquery', 'underscore', 'backbone', 'routing', 'pim/router', 'oro/translator', 'oro/mediator',
    'oro/messenger', 'oro/error', 'pim/dialog', 'oro/datagrid/action-launcher', 'require-context'],
function($, _, Backbone, routing, router, __, mediator, messenger, error, Dialog, ActionLauncher, requireContext) {
    'use strict';

    /**
     * Abstract action class. Subclasses should override execute method which is invoked when action is running.
     *
     * Triggers events:
     *  - "preExecute" before action is executed
     *  - "postExecute" after action is executed
     *
     * @export  oro/datagrid/abstract-action
     * @class   oro.datagrid.AbstractAction
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Function} */
        launcherPrototype: ActionLauncher,

        /** @property {Object} */
        launcherOptions: undefined,

        /** @property {String} */
        name: null,

        /** @property {oro.datagrid.Grid} */
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
            confirm_title: __('pim_datagrid.action.default.confirmation.title'),
            confirm_content: __('pim_datagrid.action.default.confirmation.content'),
            confirm_ok: __('pim_common.yes'),
            success: __('pim_datagrid.action.default.success'),
            error: __('pim_datagrid.action.default.error'),
            empty_selection: __('pim_datagrid.action.default.no_items')
        },

        /**
         * Initialize view
         *
         * @param {Object} options
         * @param {Object} [options.launcherOptions] Options for new instance of launcher object
         */
        initialize: function(options) {
            options = options || {};

            if (!options.datagrid) {
                throw new TypeError("'datagrid' is required");
            }
            this.datagrid = options.datagrid;

            _.each(this.messages, _.bind(function (message, key) {
                this.messages[key] = message;
            }, this));

            _.defaults(this.messages, this.defaultMessages);

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
         * @return {oro.datagrid.ActionLauncher}
         */
        createLauncher: function(options) {
            options = options || {};
            if (_.isUndefined(options.icon) && !_.isUndefined(this.icon)) {
                options.icon = this.icon;
            }
            if (_.isUndefined(options.className) && !_.isUndefined(this.className)) {
                options.className = this.className;
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
            var eventName = this.getEventName();
            mediator.once(eventName, this.executeConfiguredAction, this);
            this._confirmationExecutor(
                _.bind(
                    function() {mediator.trigger(eventName, this);},
                    this
                )
            );
        },

        getEventName: function() {
            return 'grid_action_execute:' + this.datagrid.name + ':' + this.name;
        },

        executeConfiguredAction: function(action) {
            if (action.frontend_type == 'export') {
                this._handleExport(action);
            } else if (action.frontend_type == 'ajax') {
                this._handleAjax(action);
            } else if (action.frontend_type == 'redirect') {
                this._handleRedirect(action);
            } else {
                this._handleWidget(action);
            }
        },

        _confirmationExecutor: function(callback) {
            if (this.confirmation) {
                this.getConfirmDialog(callback);
            } else {
                callback();
            }
        },

        _handleExport: function(action) {
            if (action.dispatched) {
                return;
            }

            var ExportAction = requireContext('oro/' + action.frontend_type + '-widget')

            var exportAction = new ExportAction(action)
            exportAction.run();
        },

        _handleWidget: function(action) {
            if (action.dispatched) {
                return;
            }
            action.frontend_options.url = action.frontend_options.url || this.getLinkWithParameters();
            action.frontend_options.title = action.frontend_options.title || this.label;

            var WidgetType = requireContext('oro/' + action.frontend_type + '-widget')

            var widget = new WidgetType(action.frontend_options);
            widget.render();
        },

        _handleRedirect: function(action) {
            if (action.dispatched) {
                return;
            }
            var url = action.getLinkWithParameters();
            router.redirect(url);
        },

        _handleAjax: function(action) {
            if (action.dispatched) {
                return;
            }
            action.datagrid.showLoading();
            $.ajax({
                url: action.getLink(),
                method: action.getMethod(),
                data: action.getActionParameters(),
                context: action,
                dataType: 'json',
                error: action._onAjaxError,
                success: action._onAjaxSuccess
            });
        },

        _onAjaxError: function(jqXHR, textStatus, errorThrown) {
            error.dispatch(null, jqXHR);
            this.datagrid.hideLoading();
        },

        _onAjaxSuccess: function(data, textStatus, jqXHR) {
            if (data.count) {
                this.datagrid.collection.state.totalRecords -= data.count;
            }

            this.datagrid.hideLoading();
            this.datagrid.collection.fetch();

            var defaultMessage = data.successful ? this.messages.success : this.messages.error,
                message = __(data.message) || defaultMessage;
            if (message) {
                messenger.notify(data.successful ? 'success' : 'error', message);
            }
        },

        /**
         * Get action url
         *
         * @return {String}
         * @private
         */
        getLink: function(parameters) {
            if (_.isUndefined(parameters)) {
                parameters = {};
            }
            return routing.generate(
                this.route,
                _.extend(
                    this.route_parameters,
                    parameters
                )
            );
        },

        getMethod: function () {
            return 'GET';
        },

        /**
         * Get action url with parameters added.
         *
         * @returns {String}
         */
        getLinkWithParameters: function() {
            return this.getLink(this.getActionParameters());
        },

        /**
         * Get action parameters
         *
         * @returns {Object}
         */
        getActionParameters: function() {
            return {};
        },

        /**
         * Get view for confirm modal
         *
         * @return {oro.Modal}
         */
        getConfirmDialog: function(callback) {
            return Dialog.confirm(
              this.messages.confirm_content,
              this.messages.confirm_title,
              callback,
              this.getEntityHint(true)
            );
        },

        /**
         * Get the entity type from datagrid metadata
         *
         * @param {Boolean} plural Pluralize the entity code
         */
        getEntityHint: function(plural) {
            const datagrid = this.datagrid || {};
            const entityHint = datagrid.entityHint || 'item';

            if (plural) {
                return this.getEntityPlural(entityHint);
            }

            return entityHint;
        },

        /**
         * Get the entity hint separated by dashes
         */
        getEntityCode: function() {
            const entityHint = this.getEntityHint();
            return entityHint.toLowerCase().split(' ').join('_');
        },

        /**
         * Very basic pluralize method for entity types
         *
         * Example:
         *      Product -> products
         *      Family -> families
         *
         * @return {String}
         */
        getEntityPlural: function(entityHint) {
            if (entityHint.endsWith('y')) {
                return entityHint.replace(/y$/, 'ies');
            }

            return `${entityHint.replace('_', ' ')}s`;
        }
    });
});
