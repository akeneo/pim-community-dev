/* global define */
define(['jquery', 'underscore', 'backgrid', 'oro/translator', 'oro/mediator', 'oro/loading-mask',
    'oro/grid/header', 'oro/grid/body', 'oro/grid/toolbar', 'oro/grid/action-column',
    'oro/grid/select-row-cell', 'oro/grid/select-all-header-cell',
    'oro/grid/refresh-collection-action', 'oro/grid/reset-collection-action'],
function($, _, Backgrid, __, mediator, LoadingMask, GridHeader, GridBody, Toolbar, ActionColumn, SelectRowCell,
         SelectAllHeaderCell, RefreshCollectionAction, ResetCollectionAction) {
    'use strict';

    /**
     * Basic grid class.
     *
     * Triggers events:
     *  - "rowClicked" when row of grid body is clicked
     *
     * @export  oro/grid/grid
     * @class   oro.grid.Grid
     * @extends Backgrid.Grid
     */
    return Backgrid.Grid.extend({
        /** @property {String} */
        name: 'datagrid',

        /** @property {String} */
        tagName: 'div',

        /** @property {int} */
        requestsCount: 0,

        /** @property {String} */
        className: 'clearfix',

        /** @property */
        template: _.template(
            '<div class="toolbar"></div>' +
            '<div class="container-fluid">' +
                '<div class="grid-container">' +
                    '<table class="grid table-hover table table-bordered table-condensed"></table>' +
                    '<div class="no-data"></div>' +
                    '<div class="loading-mask"></div>' +
                '</div>' +
            '</div>'
        ),

        /** @property */
        noDataTemplate: _.template('<span><%= hint %><span>'),

        /** @property {Object} */
        selectors: {
            grid:        '.grid',
            toolbar:     '.toolbar',
            noDataBlock: '.no-data',
            loadingMask: '.loading-mask',
            filterBox:   '.filter-box'
        },

        /** @property {oro.grid.Header} */
        header: GridHeader,

        /** @property {oro.grid.Body} */
        body: GridBody,

        /** @property {oro.grid.Toolbar} */
        toolbar: Toolbar,

        /** @property {oro.LoadingMask} */
        loadingMask: LoadingMask,

        /** @property {oro.grid.ActionColumn} */
        actionsColumn: ActionColumn,

        /**
         * @property {Object} Default properties values
         */
        defaults: {
            noDataHint: 'No data found.',
            noResultsHint: 'No items found during search.',
            rowClickActionClass: 'row-click-action',
            rowClassName: '',
            toolbarOptions: {addResetAction: true, addRefreshAction: true},
            rowClickAction: undefined,
            multipleSorting: true,
            rowActions: [],
            massActions: []
        },

        /**
         * Initialize grid
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.collection
         * @param {(Backbone.Collection|Array)} options.columns
         * @param {String} [options.noDataHint] Text which displayed when datagrid collection is empty
         * @param {String} [options.rowClickActionClass] CSS class for row with click action
         * @param {String} [options.rowClassName] CSS class for row
         * @param {Object} [options.toolbarOptions] Options for toolbar
         * @param {Array<oro.grid.AbstractAction>} [options.rowActions] Array of row actions prototypes
         * @param {Array<oro.grid.AbstractAction>} [options.massActions] Array of mass actions prototypes
         * @param {oro.grid.AbstractAction} [options.rowClickAction] Prototype for action that handles row click
         * @throws {TypeError} If mandatory options are undefined
         */
        initialize: function(options) {
            options = options || {};

            // Check required options
            if (!options.collection) {
                throw new TypeError("'collection' is required");
            }
            this.collection = options.collection;

            if (!options.columns) {
                throw new TypeError("'columns' is required");
            }

            // Init properties values based on options and defaults
            _.extend(this, this.defaults, options);
            this.toolbarOptions = {};
            _.extend(this.toolbarOptions, this.defaults.toolbarOptions, options.toolbarOptions);

            this.collection.multipleSorting = this.multipleSorting;

            this._initRowActions();

            if (this.rowClickAction) {
                // This option property is used in oro.grid.Body
                options.rowClassName = this.rowClickActionClass + ' ' + this.rowClassName;
            }

            options.columns.push(this._createActionsColumn());
            options.columns.unshift(this._getMassActionsColumn());

            this.loadingMask = this._createLoadingMask();
            this.toolbar = this._createToolbar();

            Backgrid.Grid.prototype.initialize.apply(this, arguments);

            // Listen and proxy events
            this._listenToCollectionEvents();
            this._listenToBodyEvents();
        },

        /**
         * Inits this.rowActions and this.rowClickAction
         *
         * @private
         */
        _initRowActions: function() {
            var rowClickActions = _.filter(this.rowActions, function(action, key) {
                if (action.prototype.runOnRowClick) {
                    delete this.rowActions[key];
                    return true;
                }
                return false;
            }, this);

            if (rowClickActions.length && !this.rowClickAction) {
                this.rowClickAction = rowClickActions[0];
            }
        },

        /**
         * Creates actions column
         *
         * @return {Backgrid.Column}
         * @private
         */
        _createActionsColumn: function() {
            return new this.actionsColumn({
                actions: this.rowActions,
                datagrid: this
            });
        },

        /**
         * Creates mass actions column
         *
         * @return {Backgrid.Column}
         * @private
         */
        _getMassActionsColumn: function() {
            if (!this.massActionsColumn) {
                this.massActionsColumn = new Backgrid.Column({
                    name: "massAction",
                    label: __("Selected Rows"),
                    renderable: !_.isEmpty(this.massActions),
                    sortable: false,
                    editable: false,
                    cell: SelectRowCell,
                    headerCell: SelectAllHeaderCell
                });
            }

            return this.massActionsColumn;
        },

        /**
         * Gets selection state
         *
         * @returns {{selectedModels: *, inset: boolean}}
         */
        getSelectionState: function() {
            var selectAllHeader = this.header.row.cells[0];
            return selectAllHeader.getSelectionState();
        },

        /**
         * Resets selection state
         */
        resetSelectionState: function() {
            var selectAllHeader = this.header.row.cells[0];
            return selectAllHeader.selectNone();
        },

        /**
         * Creates loading mask
         *
         * @return {oro.LoadingMask}
         * @private
         */
        _createLoadingMask: function() {
            return new this.loadingMask();
        },

        /**
         * Creates instance of toolbar
         *
         * @return {oro.grid.Toolbar}
         * @private
         */
        _createToolbar: function() {
            return new this.toolbar({
                collection: this.collection,
                actions: this._getToolbarActions(),
                massActions: this._getToolbarMassActions()
            });
        },

        /**
         * Get actions of toolbar
         *
         * @return {Array}
         * @private
         */
        _getToolbarActions: function() {
            var result = [];
            if (this.toolbarOptions.addRefreshAction) {
                result.push(this.getRefreshAction());
            }
            if (this.toolbarOptions.addResetAction) {
                result.push(this.getResetAction());
            }
            return result;
        },

        /**
         * Get mass actions of toolbar
         *
         * @return {Array}
         * @private
         */
        _getToolbarMassActions: function() {
            var result = [];
            _.each(this.massActions, function(action) {
                result.push(this.createMassAction(action));
            }, this);

            return result;
        },

        /**
         * Creates action
         *
         * @param {Function} actionPrototype
         * @protected
         */
        createMassAction: function(actionPrototype) {
            return new actionPrototype({
                datagrid: this,
                launcherOptions: {
                    className: 'btn'
                }
            });
        },

        /**
         * Get action that refreshes grid's collection
         *
         * @return oro.grid.RefreshCollectionAction
         */
        getRefreshAction: function() {
            if (!this.refreshAction) {
                this.refreshAction = new RefreshCollectionAction({
                    datagrid: this,
                    launcherOptions: {
                        label: 'Refresh',
                        className: 'btn',
                        iconClassName: 'icon-refresh'
                    }
                });
            }
            return this.refreshAction;
        },

        /**
         * Get action that resets grid's collection
         *
         * @return oro.grid.ResetCollectionAction
         */
        getResetAction: function() {
            if (!this.resetAction) {
                this.resetAction = new ResetCollectionAction({
                    datagrid: this,
                    launcherOptions: {
                        label: 'Reset',
                        className: 'btn',
                        iconClassName: 'icon-repeat'
                    }
                });
            }
            return this.resetAction;
        },

        /**
         * Listen to events of collection
         *
         * @private
         */
        _listenToCollectionEvents: function() {
            this.collection.on('request', function(model, xhr, options) {
                this._beforeRequest();
                var self = this;
                var always = xhr.always;
                xhr.always = function() {
                    always.apply(this, arguments);
                    self._afterRequest();
                }
            }, this);

            this.collection.on('remove', this._onRemove, this);
        },

        /**
         * Listen to events of body, proxies events "rowClicked", handle run of rowClickAction if required
         *
         * @private
         */
        _listenToBodyEvents: function() {
            this.listenTo(this.body, 'rowClicked', function(row) {
                this.trigger('rowClicked', this, row);
                this._runRowClickAction(row);
            });
        },

        /**
         * Create row click action
         *
         * @param {oro.grid.Row} row
         * @private
         */
        _runRowClickAction: function(row) {
            if (this.rowClickAction) {
                var action = new this.rowClickAction({
                    datagrid: this,
                    model: row.model
                }),
                actionConfiguration = row.model.get('action_configuration');
                if (!actionConfiguration || actionConfiguration[action.name] !== false) {
                    action.run();
                }
            }
        },

        /**
         * Renders the grid, no data block and loading mask
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();

            this.$el = this.$el.append($(this.template()));

            this.renderToolbar();
            this.renderGrid();
            this.renderNoDataBlock();
            this.renderLoadingMask();

            /**
             * Backbone event. Fired when the grid has been successfully rendered.
             * @event rendered
             */
            this.trigger("rendered");

            return this;
        },

        /**
         * Renders the grid's header, then footer, then finally the body.
         */
        renderGrid: function() {
            var $el = this.$(this.selectors.grid);

            $el.append(this.header.render().$el);
            if (this.footer) {
                $el.append(this.footer.render().$el);
            }
            $el.append(this.body.render().$el);
        },

        /**
         * Renders grid toolbar.
         */
        renderToolbar: function() {
            this.$(this.selectors.toolbar).append(this.toolbar.render().$el);
        },

        /**
         * Renders loading mask.
         */
        renderLoadingMask: function() {
            this.$(this.selectors.loadingMask).append(this.loadingMask.render().$el);
            this.loadingMask.hide();
        },

        /**
         * Render no data block.
         */
        renderNoDataBlock: function() {
            if (_.isEmpty(this.collection.state.filters)) {
                // no filters
                var dataHint = this.noDataHint;
            } else {
                // some filters exists
                var dataHint = this.noResultsHint;
            }
            this.$(this.selectors.noDataBlock).html($(this.noDataTemplate({
                hint: dataHint.replace('\n', '<br />')
            }))).hide();
            this._updateNoDataBlock();
        },

        /**
         * Triggers when collection "request" event fired
         *
         * @private
         */
        _beforeRequest: function() {
            this.requestsCount++;
            this.showLoading();
        },

        /**
         * Triggers when collection request is done
         *
         * @private
         */
        _afterRequest: function() {
            this.requestsCount--;
            if (this.requestsCount == 0) {
                this.hideLoading();
                // render block instead of update in order to change message depending on filter state
                this.renderNoDataBlock();
                /**
                 * Backbone event. Fired when data for grid has been successfully rendered.
                 * @event grid_load:complete
                 */
                mediator.trigger("grid_load:complete", this.collection);
            }
        },

        /**
         * Show loading mask and disable toolbar
         */
        showLoading: function() {
            this.loadingMask.show();
            this.toolbar.disable();
        },

        /**
         * Hide loading mask and enable toolbar
         */
        hideLoading: function() {
            this.loadingMask.hide();
            this.toolbar.enable();
        },

        /**
         * Update no data block status
         *
         * @private
         */
        _updateNoDataBlock: function() {
            if (this.collection.models.length > 0) {
                this.$(this.selectors.toolbar).show();
                this.$(this.selectors.grid).show();
                this.$(this.selectors.filterBox).show();
                this.$(this.selectors.noDataBlock).hide();
            } else {
                this.$(this.selectors.grid).hide();
                this.$(this.selectors.toolbar).hide();
                this.$(this.selectors.filterBox).hide();
                this.$(this.selectors.noDataBlock).show();
            }
        },

        /**
         * Triggers when collection "remove" event fired
         *
         * @private
         */
        _onRemove: function() {
            this.collection.fetch();
        },

        /**
         * Set additional parameter to send on server
         *
         * @param {String} name
         * @param value
         */
        setAdditionalParameter: function(name, value) {
            var state = this.collection.state;
            if (!_.has(state, 'parameters')) {
                state.parameters = {};
            }

            state.parameters[name] = value;
        },

        /**
         * Remove additional parameter
         *
         * @param {String} name
         */
        removeAdditionalParameter: function(name) {
            var state = this.collection.state;
            if (_.has(state, 'parameters')) {
                delete state.parameters[name];
            }
        }
    });
});
