'use strict';

define([
        'jquery',
        'underscore',
        'oro/tools',
        'oro/mediator',
        'oro/pageable-collection',
        'oro/datagrid/grid',
        'oro/datagrid/grid-views/view',
        'require-context'
    ],
    function ($, _, tools, mediator, PageableCollection, Grid, GridViewsView, requireContext) {

    var gridSelector = '[data-type="datagrid"]:not([data-rendered])',
        gridGridViewsSelector = '.page-title > .AknTitleContainer .span10:last',
        headerCellModuleName = 'oro/datagrid/{{type}}-header-cell',
        cellModuleName = 'oro/datagrid/{{type}}-cell',
        actionModuleName = 'oro/datagrid/{{type}}-action',
        cellTypes = {
            integer:   'integer',
            decimal:   'number',
            percent:   'number'
        },

        reservedActions = ['export', 'ajax', 'redirect', 'edit', 'delete'],

        helpers = {
            headerCellType: function (type) {
                return type + 'HeaderCell';
            },
            cellType: function (type) {
                return type + 'Cell';
            },
            actionType: function (type) {
                return type + 'Action';
            }
        },

        methods = {
            /**
             * Reads data from grid container, collects required modules and runs grid builder
             *
             * @param {Function} initBuilders
             */
            initBuilder: function (initBuilders) {
                var self = this;

                self.metadata = _.extend({
                    columns: [],
                    options: {},
                    state: {},
                    rowActions: {},
                    massActionsGroups: [],
                    massActions: {}
                }, self.$el.data('metadata'));

                self.modules = {};

                methods.collectModules.call(self);

                // load all dependencies and build grid
                tools.loadModules(self.modules, function () {
                    methods.buildGrid.call(self);
                    initBuilders();
                    methods.afterBuild.call(self);
                });
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules,
                    metadata = this.metadata,
                    moduleName = function (template, type) {
                        return template.replace('{{type}}', type);
                    };
                // header cells & cells
                _.each(metadata.columns, function (column) {
                    var headerCellType = column.headerCell;
                    if (undefined !== headerCellType) {
                        modules[helpers.headerCellType(headerCellType)] = moduleName(headerCellModuleName, headerCellType);
                    }

                    var type = column.type;
                    modules[helpers.cellType(type)] = moduleName(cellModuleName, cellTypes[type] || type);
                });
                // row actions
                _.each(_.values(metadata.rowActions), function (action) {
                    modules[helpers.actionType(action.type)] = moduleName(actionModuleName, action.type);
                });
                // default mass actions
                if (!$.isEmptyObject(metadata.massActions)) {
                    modules[helpers.actionType('mass')] = moduleName(actionModuleName, 'mass');
                }
                // mass actions
                _.each(_.values(metadata.massActions), function (massAction) {
                    if (!_.contains(reservedActions, massAction.type)) {
                        modules[helpers.actionType(massAction.type)] = moduleName(actionModuleName, massAction.type);
                    }
                });
            },

            /**
             * Build grid
             */
            buildGrid: function () {
                var options, collection, grid;

                // create collection
                options = methods.combineCollectionOptions.call(this);
                collection = new PageableCollection(this.$el.data('data'), options);

                // create grid
                options = methods.combineGridOptions.call(this);
                grid = new Grid(_.extend({collection: collection}, options));
                this.grid = grid;
                this.$el.append(grid.render().$el);

                // create grid view
                options = methods.combineGridViewsOptions.call(this);
                $(gridGridViewsSelector).append((new GridViewsView(_.extend({collection: collection}, options))).render().$el);
            },

            /**
             * After build
             */
            afterBuild: function () {
                mediator.trigger('datagrid_collection_set_after', this.grid.collection, this.$el);
            },

            /**
             * Process metadata and combines options for collection
             *
             * @returns {Object}
             */
            combineCollectionOptions: function () {
                return _.extend({
                    inputName: this.metadata.options.gridName,
                    parse: true,
                    url: '\/user\/json',
                    state: _.extend({
                        filters: {},
                        sorters: {}
                    }, this.metadata.state)
                }, this.metadata.options);
            },

            /**
             * Process metadata and combines options for datagrid
             *
             * @returns {Object}
             */
            combineGridOptions: function () {
                var columns,
                    rowActions = {},
                    massActions = {},
                    defaultOptions = {
                        sortable: false
                    },
                    modules = this.modules,
                    metadata = this.metadata;

                // columns
                columns = _.map(metadata.columns, function (cell) {
                    var cellOptionKeys = ['name', 'label', 'renderable', 'editable', 'sortable', 'headerCell'],
                        cellOptions = _.extend({}, defaultOptions, _.pick.apply(null, [cell].concat(cellOptionKeys))),
                        extraOptions = _.omit.apply(null, [cell].concat(cellOptionKeys.concat('type'))),
                        headerCellType = modules[helpers.headerCellType(cell.headerCell)],
                        cellType = modules[helpers.cellType(cell.type)];
                    if (!_.isEmpty(extraOptions)) {
                        cellOptions.extraOptions = _.extend({}, extraOptions);
                        cellType = cellType.extend(extraOptions);
                    }
                    cellOptions.headerCell = headerCellType;
                    cellOptions.cell = cellType;

                    return cellOptions;
                });

                // row actions
                _.each(metadata.rowActions, function (options, action) {
                    rowActions[action] = modules[helpers.actionType(options.type)].extend(options);
                });

                // mass actions
                _.each(metadata.massActions, function (options, action) {
                    var optionType = _.contains(reservedActions, options.type) ? 'mass' : options.type;
                    massActions[action] = modules[helpers.actionType(optionType)].extend(options);
                });


                return {
                    name: metadata.options.gridName,
                    columns: columns,
                    rowActions: rowActions,
                    massActionsGroups: metadata.massActionsGroups,
                    massActions: massActions,
                    toolbarOptions: metadata.options.toolbarOptions || {},
                    multipleSorting: metadata.options.multipleSorting || false,
                    entityHint: metadata.options.entityHint,
                    row: metadata.options.rowView ? requireContext(metadata.options.rowView) : null,
                    displayTypes: metadata.options.displayTypes,
                    manageColumns: metadata.options.manageColumns,
                    emptyGridOptions: metadata.options.emptyGridOptions
                };
            },

            /**
             * Process metadata and combines options for datagrid views
             *
             * @returns {Object}
             */
            combineGridViewsOptions: function () {
                return this.metadata.gridViews || {};
            }
        };


    /**
     * Process datagirid's metadata and creates datagrid
     *
     * @export oro/datagrid-builder
     * @name   oro.datagridBuilder
     */
    return function (builders) {
        $(gridSelector).each(function (i, el) {
            var $el = $(el);
            var gridName = (($el.data('metadata') || {}).options || {}).gridName;
            if (!gridName) {
                return;
            }
            $el.attr('data-rendered', true);

            if (!_.isArray(builders)) {
                builders = [builders];
            }

            methods.initBuilder.call({ $el: $el }, function () {
                _.each(builders, function (builder) {
                    if (!_.has(builder, 'init') || !$.isFunction(builder.init)) {
                        throw new TypeError('Builder does not have init method');
                    }
                    builder.init($el, gridName);
                });
            });
        }).end();
    };
});
