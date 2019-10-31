/*jslint nomen: true, vars: true*/
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'backgrid',
        'translator-lib',
        'oro/translator',
        'oro/mediator',
        'oro/loading-mask',
        'oro/datagrid/header',
        'oro/datagrid/body',
        'oro/datagrid/action-column',
        'oro/datagrid/select-row-cell',
        'oro/datagrid/select-all-header-cell',
        'pim/template/common/no-data',
        'pim/template/common/grid'
    ],
    function (
        $,
        _,
        Backgrid,
        Translator,
        __,
        mediator,
        LoadingMask,
        GridHeader,
        GridBody,
        ActionColumn,
        SelectRowCell,
        SelectAllHeaderCell,
        noDataTemplate,
        template
    ) {
        'use strict';

        /**
         * Basic grid class.
         *
         * Triggers events:
         *  - "rowClicked" when row of grid body is clicked
         *
         * @export  oro/datagrid/grid
         * @class   oro.datagrid.Grid
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
            template: _.template(template),

            /** @property */
            noDataTemplate: _.template(noDataTemplate),

            /** @property {Object} */
            selectors: {
                grid:        '.grid',
                noDataBlock: '.no-data',
                loadingMask: '.loading-mask',
                toolbar:     '[data-drop-zone="toolbar"]'
            },

            /** @property {oro.datagrid.Header} */
            header: GridHeader,

            /** @property {oro.datagrid.Body} */
            body: GridBody,

            /** @property {oro.LoadingMask} */
            loadingMask: LoadingMask,

            /** @property {oro.datagrid.ActionColumn} */
            actionsColumn: ActionColumn,

            /**
             * @property {Object} Default properties values
             */
            defaults: {
                rowClickActionClass: 'row-click-action',
                rowClassName:        'AknGrid-bodyRow',
                rowClickAction:      undefined,
                multipleSorting:     true,
                rowActions:          [],
                massActionsGroups:   [],
                massActions:         [],
                emptyGridOptions: null
            },

            /**
             * Initialize grid
             *
             * @param {Object} options
             * @param {Backbone.Collection} options.collection
             * @param {(Backbone.Collection|Array)} options.columns
             * @param {String} [options.rowClickActionClass] CSS class for row with click action
             * @param {String} [options.rowClassName] CSS class for row
             * @param {Array<oro.datagrid.AbstractAction>} [options.rowActions] Array of row actions prototypes
             * @param {Array<oro.datagrid.AbstractAction>} [options.massActions] Array of mass actions prototypes
             * @param {oro.datagrid.AbstractAction} [options.rowClickAction] Prototype for action that handles row click
             * @throws {TypeError} If mandatory options are undefined
             */
            initialize: function (options) {
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

                this.collection.multipleSorting = this.multipleSorting;

                this._initRowActions();

                if (this.rowClickAction) {
                    // This option property is used in oro.datagrid.Body
                    options.rowClassName = this.rowClickActionClass + ' ' + this.rowClassName;
                }

                options.columns.push(this._createActionsColumn());
                options.columns.unshift(this._getMassActionsColumn());

                this.loadingMask = this._createLoadingMask();

                Backgrid.Grid.prototype.initialize.apply(this, arguments);

                // Listen and proxy events
                this._listenToCollectionEvents();
                this._listenToBodyEvents();
                this._listenToCommands();

                mediator.trigger('grid_load:start', this.collection, this);
            },

            /**
             * Inits this.rowActions and this.rowClickAction
             *
             * @private
             */
            _initRowActions: function () {
                if (!this.rowClickAction) {
                    this.rowClickAction = _.find(this.rowActions, function (action) {
                        return Boolean(action.prototype.rowAction);
                    });
                }
            },

            /**
             * Creates actions column
             *
             * @return {Backgrid.Column}
             * @private
             */
            _createActionsColumn: function () {
                return new this.actionsColumn({
                    actions:  this.rowActions,
                    datagrid: this
                });
            },

            /**
             * Creates mass actions column
             *
             * @return {Backgrid.Column}
             * @private
             */
            _getMassActionsColumn: function () {
                if (!this.massActionsColumn) {
                    this.massActionsColumn = new Backgrid.Column({
                        name:       "massAction",
                        label:      __("Selected Rows"),
                        renderable: !_.isEmpty(this.massActions),
                        sortable:   false,
                        editable:   false,
                        cell:       SelectRowCell,
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
            getSelectionState: function () {
                var selectAllHeader = this.header.row.cells[0];
                return selectAllHeader.getSelectionState();
            },

            /**
             * Resets selection state
             */
            resetSelectionState: function () {
                var selectAllHeader = this.header.row.cells[0];
                return selectAllHeader.selectNone();
            },

            /**
             * Creates loading mask
             *
             * @return {oro.LoadingMask}
             * @private
             */
            _createLoadingMask: function () {
                return new this.loadingMask();
            },

            /**
             * Listen to events of collection
             *
             * @private
             */
            _listenToCollectionEvents: function () {
                this.collection.on('request', function (model, xhr, options) {
                    this._beforeRequest();
                    var self = this;
                    var always = xhr.always;
                    xhr.always = function () {
                        always.apply(this, arguments);
                        self._afterRequest();
                    };
                }, this);

                this.collection.on('remove', this._onRemove, this);

                this.collection.on('change', function (model) {
                    this.$el.trigger('datagrid:change:' + this.name, model);
                }.bind(this));
            },

            /**
             * Listen to events of body, proxies events "rowClicked", handle run of rowClickAction if required
             *
             * @private
             */
            _listenToBodyEvents: function () {
                this.listenTo(this.body, 'rowClicked', function (row) {
                    this.trigger('rowClicked', this, row);
                    this._runRowClickAction(row);
                });
            },

            /**
             * Create row click action
             *
             * @param {oro.datagrid.Row} row
             * @private
             */
            _runRowClickAction: function (row) {
                if (this.rowClickAction) {
                    var action = new this.rowClickAction({
                            datagrid: this,
                            model:    row.model
                        }),
                        actionConfiguration = row.model.get('action_configuration');
                    if (!actionConfiguration || actionConfiguration[action.name] !== false) {
                        action.run();
                    }
                }
            },

            /**
             * Listen to commands on mediator
             */
            _listenToCommands: function () {
                var grid = this;

                mediator.clear('datagrid:setParam:' + grid.name);
                mediator.clear('datagrid:removeParam:' + grid.name);
                mediator.clear('datagrid:restoreState:' + grid.name);
                mediator.clear('datagrid:doRefresh:' + grid.name);

                mediator.on('datagrid:setParam:' + grid.name, function (param, value) {
                    grid.setAdditionalParameter(param, value);
                });

                mediator.on('datagrid:removeParam:' + grid.name, function (param) {
                    grid.removeAdditionalParameter(param);
                });

                mediator.on('datagrid:restoreState:' + grid.name, function (columnName, dataField, included, excluded) {
                    grid.collection.each(function (model) {
                        if (_.indexOf(included, model.get(dataField)) !== -1) {
                            model.set(columnName, true);
                        }
                        if (_.indexOf(excluded, model.get(dataField)) !== -1) {
                            model.set(columnName, false);
                        }
                    });
                });

                mediator.on('datagrid:doRefresh:' + grid.name, this.refreshCollection.bind(this));
            },

            /**
             * Renders the grid, no data block and loading mask
             *
             * @return {*}
             */
            render: function () {
                this.$el.empty();

                this.$el = this.$el.append($(this.template({
                    hasCheckbox: this.massActionsColumn.get('renderable') === true
                })));

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
            renderGrid: function () {
                var $el = this.$(this.selectors.grid);

                $el.append(this.header.render().$el);
                if (this.footer) {
                    $el.append(this.footer.render().$el);
                }
                $el.append(this.body.render().$el);
            },

            /**
             * Renders loading mask.
             */
            renderLoadingMask: function () {
                this.$(this.selectors.loadingMask).append(this.loadingMask.render().$el);
                this.loadingMask.hide();
            },

            /**
             * Returns the messages to display when there is no results.
             *
             * @returns {{hint, subHint: *, imageClass: string}}
             */
            getDefaultNoDataOptions() {
                const entityHint = (this.entityHint ?
                    this.entityHint.replace(/_/, ' ') :
                    __('pim_datagrid.entity_hint')
                ).toLowerCase();
                let key = _.isEmpty(this.collection.state.filters) ?
                    'pim_datagrid.no_entities' :
                    'pim_datagrid.no_results';

                if (Translator.has('jsmessages:' + key + '.' + entityHint)) {
                    key += '.' + entityHint;
                }

                const hint = __(key, {entityHint: entityHint}).replace('\n', '<br />');
                const subHint = 'pim_datagrid.no_results_subtitle';

                return { hint, subHint, imageClass: '', __ };
            },

            /**
             * Render no data block.
             */
            renderNoDataBlock: function () {
                const customOptions = this.emptyGridOptions;
                let options = this.getDefaultNoDataOptions();

                if (null !== customOptions && undefined !== customOptions) {
                    options = customOptions;
                    options.__ = __;
                }

                this.$(this.selectors.noDataBlock).html($(this.noDataTemplate(options))).hide();
                this._updateNoDataBlock();
            },

            /**
             * Refresh datagrid collection, triggers for datagrid:doRefresh events
             */
            refreshCollection: function() {
                this.setAdditionalParameter('refresh', true);
                this.collection.fetch();
                this.removeAdditionalParameter('refresh');
            },

            /**
             * Triggers when collection "request" event fired
             *
             * @private
             */
            _beforeRequest: function () {
                this.requestsCount += 1;
                this.showLoading();
            },

            /**
             * Triggers when collection request is done
             *
             * @private
             */
            _afterRequest: function () {
                this.requestsCount -= 1;
                if (this.requestsCount === 0) {
                    this.hideLoading();
                    // render block instead of update in order to change message depending on filter state
                    this.renderNoDataBlock();
                    /**
                     * Backbone event. Fired when data for grid has been successfully rendered.
                     * @event grid_load:complete
                     */
                    mediator.trigger("grid_load:complete", this.collection, this);
                }
            },

            /**
             * Show loading mask and disable toolbar
             */
            showLoading: function () {
                this.loadingMask.show();
            },

            /**
             * Hide loading mask and enable toolbar
             */
            hideLoading: function () {
                this.loadingMask.hide();
            },

            /**
             * Update no data block status
             *
             * @private
             */
            _updateNoDataBlock: function () {
                if (this.collection.models.length > 0) {
                    this.$(this.selectors.grid).show();
                    $(this.selectors.toolbar).show();
                    this.$(this.selectors.noDataBlock).hide();
                } else {
                    this.$(this.selectors.grid).hide();
                    $(this.selectors.toolbar).hide();
                    this.$(this.selectors.noDataBlock).show();
                }
            },

            /**
             * Triggers when collection "remove" event fired
             *
             * @private
             */
            _onRemove: function () {
                this.collection.fetch();
            },

            /**
             * Set additional parameter to send on server
             *
             * @param {String} name
             * @param value
             */
            setAdditionalParameter: function (name, value) {
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
            removeAdditionalParameter: function (name) {
                var state = this.collection.state;
                if (_.has(state, 'parameters')) {
                    delete state.parameters[name];
                }
            }
        });
    });
